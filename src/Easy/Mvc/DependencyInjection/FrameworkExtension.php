<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\DependencyInjection;

use Easy\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * FrameworkExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class FrameworkExtension extends Extension
{

    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load('web.yml');
        $loader->load('services.yml');

        // A translator must always be registered (as support is included by
        // default in the Form component). If disabled, an identity translator
        // will be used and everything will still work as expected.
        $loader->load('translation.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['secret'])) {
            $container->setParameter('kernel.secret', $config['secret']);
        }

        $container->setParameter('kernel.http_method_override', $config['http_method_override']);

        $container->setParameter('kernel.trusted_proxies', $config['trusted_proxies']);
        $container->setParameter('kernel.default_locale', $config['default_locale']);

        if (isset($config['session'])) {
            $this->registerSessionConfiguration($config['session'], $container, $loader);
        }

        if (isset($config['default_locale'])) {
            $this->registerLocaleConfiguration($config, $container);
        }

        if (isset($config['templating'])) {
            $this->registerTempaltingConfiguration($config['templating'], $container, $loader);
        }

        if (isset($config['router'])) {
            $this->registerRouterConfiguration($config['router'], $container, $loader);
        }

        if (isset($config['view'])) {
            $this->registerViewConfiguration($config['view'], $container, $loader);
        }

        if (isset($config['serializer']) && $config['serializer']['enabled']) {
            $loader->load('serializer.yml');
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    /**
     * Loads the locale configuration.
     * @param array $config    A session configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    private function registerLocaleConfiguration(array $config, ContainerBuilder $container)
    {
        $locale = $container->get("locale");
        $locale->setLocale($config['default_locale']);

        if (isset($config['session'])) {
            $locale->setSession($container->get("session"));
        }

        $locale->configLocale();
    }

    /**
     * Loads the session configuration.
     *
     * @param array $config    A session configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param YamlFileLoader $loader    An YamlFileLoader instance
     */
    private function registerSessionConfiguration(array $config, ContainerBuilder $container, YamlFileLoader $loader)
    {
        $loader->load('session.yml');

        // session storage
        $container->setAlias('session.storage', $config['storage_id']);
        $options = array();
        foreach (array('name', 'cookie_lifetime', 'cookie_path', 'cookie_domain', 'cookie_secure', 'cookie_httponly', 'gc_maxlifetime', 'gc_probability', 'gc_divisor') as $key) {
            if (isset($config[$key])) {
                $options[$key] = $config[$key];
            }
        }

        $container->setParameter('session.storage.options', $options);

        // session handler (the internal callback registered with PHP session management)
        if (null == $config['handler_id']) {
            // Set the handler class to be null
            $container->getDefinition('session.storage.native')->replaceArgument(1, null);
        } else {
            $container->setAlias('session.handler', $config['handler_id']);
        }

        $container->setParameter('session.save_path', $config['save_path']);

        $this->addClassesToCompile(array(
            'Easy\\Mvc\\EventListener\\SessionListener',
            $container->getDefinition('session')->getClass(),
        ));

        if ($container->hasDefinition($config['storage_id'])) {
            $this->addClassesToCompile(array(
                $container->findDefinition('session.storage')->getClass(),
            ));
        }
    }

    /**
     * Loads the templating configuration.
     *
     * @param array $config    A session configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param YamlFileLoader $loader    An YamlFileLoader instance
     */
    private function registerTempaltingConfiguration(array $config, ContainerBuilder $container, YamlFileLoader $loader)
    {
        $loader->load('templating.yml');

        $container->setParameter('templating.engines', $config['engines']);
        $engines = array_map(function ($engine) {
            return new Reference('templating.engine.' . $engine);
        }, $config['engines']);

        // Use a delegation unless only a single engine was registered
        if (1 === count($engines)) {
            $container->setAlias('templating', (string)reset($engines));
        } else {
            foreach ($engines as $engine) {
                $container->getDefinition('templating.engine.delegating')->addMethodCall('addEngine', array($engine));
            }
            $container->setAlias('templating', 'templating.engine.delegating');
        }
    }

    /**
     * Loads the router configuration.
     *
     * @param array $config    A router configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param YamlFileLoader $loader    An YamlFileLoader instance
     */
    private function registerRouterConfiguration(array $config, ContainerBuilder $container, YamlFileLoader $loader)
    {
        $loader->load('routing.yml');

        $container->setParameter('router.resource', $config['resource']);
        $container->setParameter('router.cache_class_prefix', $container->getParameter('kernel.name') . ucfirst($container->getParameter('kernel.environment')));
        $router = $container->findDefinition('router.default');
        $argument = $router->getArgument(2);
        $argument['strict_requirements'] = $config['strict_requirements'];
        if (isset($config['type'])) {
            $argument['resource_type'] = $config['type'];
        }
        $router->replaceArgument(2, $argument);

        $container->setParameter('request_listener.http_port', $config['http_port']);
        $container->setParameter('request_listener.https_port', $config['https_port']);
    }

}
