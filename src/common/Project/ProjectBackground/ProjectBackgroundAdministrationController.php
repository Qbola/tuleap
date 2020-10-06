<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Project\ProjectBackground;

use HTTPRequest;
use TemplateRendererFactory;
use Tuleap\Layout\BaseLayout;
use Tuleap\Layout\IncludeAssets;
use Tuleap\Project\Admin\Routing\AdministrationLayoutHelper;
use Tuleap\Project\Admin\Routing\LayoutHelper;
use Tuleap\Request\DispatchableWithBurningParrot;
use Tuleap\Request\DispatchableWithRequest;

final class ProjectBackgroundAdministrationController implements DispatchableWithRequest, DispatchableWithBurningParrot
{
    /**
     * @var LayoutHelper
     */
    private $layout_helper;
    /**
     * @var \TemplateRenderer
     */
    private $renderer;
    /**
     * @var IncludeAssets
     */
    private $banner_assets;
    /**
     * @var ProjectBackgroundRetriever
     */
    private $background_retriever;

    public function __construct(
        LayoutHelper $layout_helper,
        \TemplateRenderer $renderer,
        IncludeAssets $banner_assets,
        ProjectBackgroundRetriever $background_retriever
    ) {
        $this->layout_helper        = $layout_helper;
        $this->renderer             = $renderer;
        $this->banner_assets        = $banner_assets;
        $this->background_retriever = $background_retriever;
    }

    public static function buildSelf(): self
    {
        return new self(
            AdministrationLayoutHelper::buildSelf(),
            TemplateRendererFactory::build()->getRenderer(__DIR__ . '/../../../templates/project/admin/background/'),
            new IncludeAssets(__DIR__ . '/../../../www/assets/core', '/assets/core'),
            new ProjectBackgroundRetriever(new ProjectBackgroundDao())
        );
    }

    public function process(HTTPRequest $request, BaseLayout $layout, array $variables)
    {
        $layout->includeFooterJavascriptFile($this->banner_assets->getFileURL('ckeditor.js'));
        $layout->includeFooterJavascriptFile($this->banner_assets->getFileURL('project/project-admin-banner.js'));

        $callback = function (\Project $project, \PFUser $current_user): void {
            $backgrounds = $this->background_retriever->getBackgrounds($project);
            $this->renderer->renderToPage(
                'administration',
                new ProjectBackgroundAdministrationPresenter($backgrounds),
            );
        };
        $this->layout_helper->renderInProjectAdministrationLayout(
            $request,
            $variables['id'],
            _('Project background'),
            'background',
            $callback
        );
    }
}
