<?php

/**
 * Mapping to replace strings via a regex callback in converted themes of Omeka S.
 * It can be completed.
 *
 * Regex are never perfect, of course. They are designed for normal themes and
 * not for plugins (get_view()). Parsing errors are generally related to
 * comments /¯* *_/ or multiline functions.
 *
 * @note When an error occurs, check the default view in application/view and
 * use directly the syntax and functions of Omeka S.
 *
 * @internal This file is merged during the init of processors of the plugins.
 */

 if (!function_exists('match_strtolower')) {

    function match_strtolower($matches)
    {
        return $matches[1] . strtolower($matches[2]) . $matches[3];
    }

    function match_get_theme_option($matches)
    {
        $word = $matches[2];

        // See Theme::getOption() or get_theme_option() or Inflector::underscore().
        $themeOptionName = strtolower(preg_replace('/[^A-Z^a-z^0-9]+/', '_',
            preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
            preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $word))));


        return $matches[1] . $themeOptionName . $matches[3];
    }

}

return array(
    // Theme options may be the label of the field, that should be converted
    // lowercase and space as "_", so in a two step process (until five words).
    '~(\$this->themeSetting\([\'"])([\w\s_]+)([\'"]\))~' => 'match_get_theme_option',
);
