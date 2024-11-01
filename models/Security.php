<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class WSC_Models_Security {
    public $root_path;
    public $config_file;

    public function __construct() {
        $this->root_path = WSC_Classes_Tools::getRootPath();

        if (WSC_Classes_Tools::isApache() || WSC_Classes_Tools::isLitespeed()) {
            $this->config_file = $this->root_path . '.htaccess';
        } else {
            $this->config_file = false;
        }
    }

    public function getConfFile() {
        return $this->config_file;
    }

    /**
     * Check if the config file is writable
     * @param string $config_file
     * @return bool
     */
    public function isConfigWritable($config_file = null) {
        //get the global config file if not specified
        if (!isset($config_file)) {
            $config_file = $this->getConfFile();
        }

        if ($config_file) {
            if (!file_exists($config_file)) {
                if (!is_writable(dirname($config_file))) {
                    return false;
                }
                if (!touch($config_file)) {
                    return false;
                }
            } elseif (!is_writeable($config_file)) {
                return false;
            }
        }
        return true;
    }

    public function writeToFile($rules, $header = 'WSC_RULES') {
        if ($this->getConfFile()) {
            if (WSC_Classes_Tools::isApache() || WSC_Classes_Tools::isLitespeed()) {
                return $this->writeInHtaccess($rules, $header);
            }
        }
        return false;
    }

    public function replaceToFile($old, $new, $file) {
        if (!$this->isConfigWritable($file)) {
            return false;
        }

        $found = false;
        $lines = file($file);

        foreach ((array)$lines as $line) {
            if (preg_match("/$old/", $line)) {
                $found = true;
                break;
            }
        }

exit($new);
        if ($found) {
            $fd = fopen($file, 'w');
            foreach ((array)$lines as $line) {
                if (!preg_match("/$old/", $line)) {
                    fputs($fd, $line);
                } elseif ($new <> '') {
                    fputs($fd, $new);
                }
            }
            fclose($fd);
        }

        return $found;
    }

    public function writeInHtaccess($rules, $header = 'WSC_RULES') {
        if (WSC_Classes_Tools::isModeRewrite()) {
            return $this->insertWithMarkers($header, $rules);
        }
        return false;
    }

    /**
     * Inserts an array of strings into a file (.htaccess ), placing it between
     * BEGIN and END markers.
     *
     * Replaces existing marked info. Retains surrounding
     * data. Creates file if none exists.
     *
     * @param string $marker The marker to alter.
     * @param array|string $insertion The new content to insert.
     * @return bool True on write success, false on failure.
     */
    public function insertWithMarkers($marker, $insertion) {

        if (!$this->isConfigWritable()) {
            return false;
        }

        if (!is_array($insertion)) {
            $insertion = explode("\n", $insertion);
        }

        $start_marker = "# BEGIN {$marker}";
        $end_marker = "# END {$marker}";

        $fp = fopen($this->getConfFile(), 'r+');
        if (!$fp) {
            return false;
        }

        // Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
        flock($fp, LOCK_EX);

        $lines = array();
        while (!feof($fp)) {
            $lines[] = rtrim(fgets($fp), "\r\n");
        }

        // Split out the existing file into the preceding lines, and those that appear after the marker
        $pre_lines = $post_lines = $existing_lines = array();
        $found_marker = $found_end_marker = false;
        foreach ($lines as $line) {
            if (!$found_marker && false !== strpos($line, $start_marker)) {
                $found_marker = true;
                continue;
            } elseif (!$found_end_marker && false !== strpos($line, $end_marker)) {
                $found_end_marker = true;
                continue;
            }
            if (!$found_marker) {
                $pre_lines[] = $line;
            } elseif ($found_marker && $found_end_marker) {
                $post_lines[] = $line;
            } else {
                $existing_lines[] = $line;
            }
        }

        // Check to see if there was a change
        if ($existing_lines === $insertion) {
            flock($fp, LOCK_UN);
            fclose($fp);

            return true;
        }

        // Generate the new file data
        if (!$found_marker) {
            $new_file_data = implode("\n", array_merge(
                array($start_marker),
                $insertion,
                array($end_marker),
                $pre_lines
            ));
        } else {
            $new_file_data = implode("\n", array_merge(
                $pre_lines,
                array($start_marker),
                $insertion,
                array($end_marker),
                $post_lines
            ));
        }

        // Write to the start of the file, and truncate it to that length
        fseek($fp, 0);
        $bytes = fwrite($fp, $new_file_data);
        if ($bytes) {
            ftruncate($fp, ftell($fp));
        }
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return (bool)$bytes;
    }

}
