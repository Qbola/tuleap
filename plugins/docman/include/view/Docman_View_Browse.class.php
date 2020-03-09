<?php
/**
 * Copyright © Enalean, 2011 - Present. All Rights Reserved.
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

use Tuleap\Docman\View\DocmanViewURLBuilder;

/* abstract */ class Docman_View_Browse extends Docman_View_Display
{

    /**
     * @access: protected
     */
    public function _getTitle($params)
    {
        // If a title is defined in the report, use it instead of the  default one
        $title = $this->getTitleWhenFilterIsSet($params);
        if ($title === false) {
            return parent::_getTitle($params);
        }

        return $title;
    }

    protected function getUnconvertedTitle($params)
    {
        // If a title is defined in the report, use it instead of the
        // default one
        $title = $this->getTitleWhenFilterIsSet($params);
        if ($title === false) {
            return parent::getUnconvertedTitle($params);
        }

        return $title;
    }

    private function getTitleWhenFilterIsSet(array $params)
    {
        if (! isset($params['filter']) || $params['filter'] === null) {
            return false;
        }

        if ($params['filter']->getTitle() === null && trim($params['filter']->getTitle()) === '') {
            return false;
        }

        return htmlentities($params['filter']->getTitle(), ENT_COMPAT, 'UTF-8');
    }

    /* protected */ public function _mode($params)
    {
        $html = '';
        // No mode selector in printer version
        if (isset($params['pv']) && $params['pv'] > 0) {
            // Close table opened in method 'breadCrumbs' in 'Display' class.
            $html .= '</tr>';
            $html .= '</table>';
            echo $html;
            return;
        }

        $html .= '<td align="right">';
        $html .= '<form action="'. $params['default_url'] .'" method="POST">';
        $html .= '<span id="docman_browse_viewas">';
        $html .= $GLOBALS['Language']->getText('plugin_docman', 'browse_viewas') .' ';
        $html .= '<input type="image"
                         name="selected_view[Tree]"
                         src="'. $this->_controller->plugin->getThemePath() .'/images/ic/view-Tree.png"
                         title="'. $GLOBALS['Language']->getText('plugin_docman', 'view_Tree') .'"
                         alt="'. $GLOBALS['Language']->getText('plugin_docman', 'view_Tree') .'"
                         /> ';
        $html .= '<input type="image"
                         name="selected_view[Icons]"
                         src="'. $this->_controller->plugin->getThemePath() .'/images/ic/view-Icons.png"
                         title="'. $GLOBALS['Language']->getText('plugin_docman', 'view_Icons') .'"
                         alt="'. $GLOBALS['Language']->getText('plugin_docman', 'view_Icons') .'"
                         /> ';
        $html .= '<input type="image"
                         name="selected_view[Table]"
                         src="'. $this->_controller->plugin->getThemePath() .'/images/ic/view-Table.png"
                         title="'. $GLOBALS['Language']->getText('plugin_docman', 'view_Table') .'"
                         alt="'. $GLOBALS['Language']->getText('plugin_docman', 'view_Table') .'"
                         /> ';
        $html .= '</span>';
        $html .= '<input type="hidden" name="action" value="change_view" />';
        $html .= '<input type="hidden" name="id" value="'. $params['item']->getId() .'" />';
        $html .= '</form>';
        $html .= '</td>';

        // Close table opened in method 'breadCrumbs' in 'Display' class.
        $html .= '</tr>';
        $html .= '</table>';

        echo $html;
    }

    public $dfltParams = null;
    public function _getDefaultUrlParams($params)
    {
        if ($this->dfltParams === null) {
            $this->dfltParams = array('action' => 'search',
                                      'id'     => $params['item']->getId());

            $this->_initSearchAndSortParams($params);
        }
        return array_merge($this->dfltParams, $this->dfltSearchParams, $this->dfltSortParams);
    }

    public function _buildSearchUrl($params, $extraParams = array())
    {
        $parameters = array_merge($this->_getDefaultUrlParams($params), $extraParams);
        return DocmanViewURLBuilder::buildActionUrl($params['item'], $params, $parameters);
    }

    /**
     * @access: protected
     */
    public function _filter($params)
    {
        $html = '';

        // No filter in printer version
        if (isset($params['pv']) && $params['pv'] > 0) {
            return;
        }

        $html .= "<!-- Filters -->\n";
        $html .= '<div class="docman_filters">';
        if (isset($params['filter']) && $params['filter'] !== null) {
            $htmlReport = new Docman_ReportHtml($params['filter'], $this, $params['default_url']);
            $html .= $htmlReport->toHtml($params);
        }
        $html .= "</div>\n";

        echo $html;
    }

    public function getActionOnIconForFolder()
    {
        return 'show';
    }
    public function getClassForFolderLink()
    {
        return '';
    }

    /* static */ public function getItemClasses($params)
    {
        $li_classes = 'docman_item';
        if (isset($params['is_last']) && $params['is_last']) {
            $li_classes .= '_last';
        }
        return $li_classes;
    }

    public static function isViewAllowed($view)
    {
        //List is part of SOAP api
        return in_array($view, array_merge(array_keys(Docman_View_Browse::getDefaultViews()), array('List')));
    }
    /* static */ public function getViewForCurrentUser($group_id, $report = '')
    {
        if ($report != '') {
            $pref = $report;
        } else {
            $pref = user_get_preference(PLUGIN_DOCMAN_VIEW_PREF .'_'. $group_id);
            if (!$pref) {
                $sBo = Docman_SettingsBo::instance($group_id);
                $pref = $sBo->getView();
            }
        }
        if (!$pref || !Docman_View_Browse::isViewAllowed($pref)) {
            $pref = 'Tree';
        }
        return $pref;
    }

    public static function getDefaultViews()
    {
        return array('Tree'   => 'Tree',
                     'Icons'  => 'Icons',
                     'Table'  => 'Table',
        );
    }
}
