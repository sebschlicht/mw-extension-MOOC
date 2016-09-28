<?php

/**
 * Hooks for MOOC extension.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Rene Pickhardt ([User:renepick]), Sebastian Schlicht (sebastian@jablab.de [User:sebschlicht])
 * @license GPLv2
 */
class MOOCHooks {

    /**
     * Registers parser functions for magic keywords.
     *
     * @param Parser $parser            
     */
    public static function onParserFirstCallInit(Parser &$parser) {}

    /**
     *
     * @param array $vars            
     */
    public static function onResourceLoaderGetConfigVars(array &$vars) {
        $vars['wgMOOC'] = [
            'userAgentName' => 'MoocBot',
            'userAgentUrl' => 'https://en.wikiversity.org/wiki/User:Sebschlicht',
            'userAgentMailAddress' => 'sebschlicht@uni-koblenz.de',
            'version' => '0.1'
        ];
        
        return true;
    }
}
