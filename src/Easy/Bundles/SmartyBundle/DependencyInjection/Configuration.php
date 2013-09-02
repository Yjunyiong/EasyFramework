<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SmartyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the SmartyBundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     *
     * Example configuration (YAML):
     * <code>
     * smarty:
     *
     *     # Smarty options
     *     options:
     *         cache_dir:     %kernel.cache_dir%/smarty/cache
     *         compile_dir:   %kernel.cache_dir%/smarty/templates_c
     *         config_dir:    %kernel.root_dir%/config/smarty
     *         template_dir:  %kernel.root_dir%/Resources/views
     *         use_sub_dirs:  true
     * </code>
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('smarty');

        $rootNode
            ->treatNullLike(array('enabled' => true))
            ->end();

        $this->addGlobalsSection($rootNode);
        $this->addSmartyOptions($rootNode);

        return $treeBuilder;
    }

    /**
     * Template globals.
     */
    protected function addGlobalsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('global')
            ->children()
            ->arrayNode('globals')
            ->useAttributeAsKey('key')
            ->prototype('array')
            ->beforeNormalization()
            ->ifTrue(function ($v) {
                return is_string($v) && '@' === substr($v, 0, 1);
            })
            ->then(function ($v) {
                return array('id' => substr($v, 1), 'type' => 'service');
            })
            ->end()
            ->beforeNormalization()
            ->ifTrue(function ($v) {
                if (is_array($v)) {
                    $keys = array_keys($v);
                    sort($keys);

                    return $keys !== array('id', 'type') && $keys !== array('value');
                }

                return true;
            })
            ->then(function ($v) {
                return array('value' => $v);
            })
            ->end()
            ->children()
            ->scalarNode('id')->end()
            ->scalarNode('type')
            ->validate()
            ->ifNotInArray(array('service'))
            ->thenInvalid('The %s type is not supported')
            ->end()
            ->end()
            ->variableNode('value')->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }

    /**
     * Smarty options.
     *
     * The whole list can be seen here: {@link http://www.smarty.net/docs/en/api.variables.tpl}
     */
    protected function addSmartyOptions(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
            ->arrayNode('options')
            ->canBeUnset()
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('allow_php_templates')->end()
            ->scalarNode('auto_literal')->end()
            ->arrayNode('autoload_filters')
            ->info('filters that you wish to load on every template invocation')
            ->canBeUnset()
            ->children()
            ->arrayNode('pre')
            ->example(array('trim', 'stamp'))
            ->canBeUnset()
            ->beforeNormalization()
            ->ifTrue(function ($v) {
                return !is_array($v);
            })
            ->then(function ($v) {
                return array($v);
            })
            ->end()
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('post')
            ->example(array('add_header_comment'))
            ->canBeUnset()
            ->beforeNormalization()
            ->ifTrue(function ($v) {
                return !is_array($v);
            })
            ->then(function ($v) {
                return array($v);
            })
            ->end()
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('output')
            ->example(array('convert'))
            ->canBeUnset()
            ->beforeNormalization()
            ->ifTrue(function ($v) {
                return !is_array($v);
            })
            ->then(function ($v) {
                return array($v);
            })
            ->end()
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->scalarNode('cache_dir')->defaultValue('%kernel.cache_dir%/smarty/cache')->cannotBeEmpty()->end()
            ->scalarNode('cache_id')->end()
            ->scalarNode('cache_lifetime')->end()
            ->scalarNode('cache_locking')->end()
            ->scalarNode('cache_modified_check')->end()
            ->scalarNode('caching')->end()
            ->scalarNode('caching_type')->end()
            ->scalarNode('compile_check')->end()
            ->scalarNode('compile_dir')->defaultValue('%kernel.cache_dir%/smarty/templates_c')->cannotBeEmpty()->end()
            ->scalarNode('compile_id')->end()
            ->scalarNode('compile_locking')->end()
            ->scalarNode('compiler_class')->end()
            ->scalarNode('config_booleanize')->end()
            ->scalarNode('config_dir')->defaultValue('%kernel.root_dir%/config/smarty')->cannotBeEmpty()->end()
            ->scalarNode('config_overwrite')->end()
            ->scalarNode('config_read_hidden')->end()
            ->scalarNode('debug_tpl')->end()
            ->scalarNode('debugging')->end()
            ->scalarNode('debugging_ctrl')->end()
            ->scalarNode('default_config_type')->end()
            ->scalarNode('default_modifiers')->end()
            ->scalarNode('default_resource_type')->defaultValue('file')->end()
            ->scalarNode('default_config_handler_func')->end()
            ->scalarNode('default_template_handler_func')->end()
            ->scalarNode('direct_access_security')->end()
            ->scalarNode('error_reporting')->end()
            ->scalarNode('escape_html')->end()
            ->scalarNode('force_cache')->end()
            ->scalarNode('force_compile')->end()
            ->scalarNode('left_delimiter')->end()
            ->scalarNode('locking_timeout')->end()
            ->scalarNode('merge_compiled_includes')->end()
            ->scalarNode('php_handling')->end()
            ->scalarNode('plugins_dir')->end()
            ->scalarNode('right_delimiter')->end()
            ->scalarNode('smarty_debug_id')->end()
            ->scalarNode('template_dir')->defaultValue('%kernel.root_dir%/Resources/views')->cannotBeEmpty()->end()
            ->scalarNode('trusted_dir')->end()
            ->scalarNode('use_include_path')->defaultFalse()->end()
            ->scalarNode('use_sub_dirs')->defaultTrue()->end()
            ->end()
            ->end()
            ->end();
    }

}