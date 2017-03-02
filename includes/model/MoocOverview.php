<?php

/**
 * MOOC itself with all its lessons and units it contains.
 *
 * @author Sebastian Schlicht <sebastian@jablab.de>
 *
 * @file
 */
class MoocOverview extends MoocItem {

    /**
     * MOOC entity type for MOOCs
     */
    const ENTITY_TYPE_MOOC = 'mooc';

    public function toJson() {
        return [
            self::JFIELD_TYPE => $this->type,
        ];
    }
}