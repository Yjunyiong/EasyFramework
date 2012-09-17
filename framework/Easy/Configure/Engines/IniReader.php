<?php

/**
 * FROM CAKEPHP
 * 
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 1.5.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Configure\Engines;

use Easy\Configure\IConfigReader;

/**
 * Ini file configuration parser.  Since IniReader uses parse_ini_file underneath,
 * you should be aware that this class shares the same behavior, especially with
 * regards to boolean and null values.
 *
 * In addition to the native parse_ini_file features, IniReader also allows you
 * to create nested array structures through usage of `.` delimited names.  This allows
 * you to create nested arrays structures in an ini config file. For example:
 *
 * `db.password = secret` would turn into `array('db' => array('password' => 'secret'))`
 *
 * You can nest properties as deeply as needed using `.`'s. In addition to using `.` you
 * can use standard ini section notation to create nested structures:
 *
 * {{{
 * [section]
 * key = value
 * }}}
 *
 * Once loaded into Configure, the above would be accessed using:
 *
 * `Configure::read('section.key');
 *
 * You can combine `.` separated values with sections to create more deeply
 * nested structures.
 *
 * IniReader also manipulates how the special ini values of
 * 'yes', 'no', 'on', 'off', 'null' are handled. These values will be
 * converted to their boolean equivalents.
 *
 * @package       Easy.Configure.Engines
 * @see http://php.net/parse_ini_file
 */
class IniReader implements IConfigReader
{

    /**
     * The path to read ini files from.
     *
     * @var array
     */
    protected $_path;

    /**
     * The section to read, if null all sections will be read.
     *
     * @var string
     */
    protected $_section;

    /**
     * Build and construct a new ini file parser. The parser can be used to read
     * ini files that are on the filesystem.
     *
     * @param string $path Path to load ini config files from.
     * @param string $section Only get one section, leave null to parse and fetch
     *     all sections in the ini file.
     */
    public function __construct($path, $section = null)
    {
        $this->_path = $path;
        $this->_section = $section;
    }

    /**
     * Read an ini file and return the results as an array.
     *
     * @param string $file Name of the file to read. The chosen file
     *    must be on the reader's path.
     * @return array
     * @throws ConfigureException
     */
    public function read($file)
    {
        $filename = $this->_path . $file;
        if (!file_exists($filename)) {
            $filename .= '.ini';
            if (!file_exists($filename)) {
                throw new ConfigureException(__('Could not load configuration files: %s or %s', substr($filename, 0, -4), $filename));
            }
        }
        $contents = parse_ini_file($filename, true);
        if (!empty($this->_section) && isset($contents[$this->_section])) {
            $values = $this->_parseNestedValues($contents[$this->_section]);
        } else {
            $values = array();
            foreach ($contents as $section => $attribs) {
                if (is_array($attribs)) {
                    $values[$section] = $this->_parseNestedValues($attribs);
                } else {
                    $parse = $this->_parseNestedValues(array($attribs));
                    $values[$section] = array_shift($parse);
                }
            }
        }
        return $values;
    }

    /**
     * parses nested values out of keys.
     *
     * @param array $values Values to be exploded.
     * @return array Array of values exploded
     */
    protected function _parseNestedValues($values)
    {
        foreach ($values as $key => $value) {
            if ($value === '1') {
                $value = true;
            }
            if ($value === '') {
                $value = false;
            }

            if (strpos($key, '.') !== false) {
                $values = Set::insert($values, $key, $value);
                //$this->_parseNestedValues($values);
            } else {
                $values[$key] = $value;
            }
        }
        return $values;
    }

}
