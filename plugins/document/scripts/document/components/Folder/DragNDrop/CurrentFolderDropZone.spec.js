/*
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

import { shallowMount } from "@vue/test-utils";
import localVue from "../../../helpers/local-vue";
import { createStoreMock } from "../../../helpers/store-wrapper.spec-helper.js";
import CurrentFolderDropZone from "./CurrentFolderDropZone.vue";

describe("CurrentFolderDropZone", () => {
    let current_folder_drop_zone_factory, store;
    beforeEach(() => {
        const state = {
            max_files_dragndrop: 10,
            max_size_upload: 10000
        };

        const store_options = {
            state
        };

        store = createStoreMock(store_options);

        store.getters.current_folder_title = "My folder";

        current_folder_drop_zone_factory = (props = {}) => {
            return shallowMount(CurrentFolderDropZone, {
                localVue,
                propsData: { ...props },
                mocks: { $store: store }
            });
        };
    });

    it(`Given user has write permission
        When we display the drop zone
        Then user should have a success message`, () => {
        const wrapper = current_folder_drop_zone_factory({
            user_can_dragndrop_in_current_folder: true
        });

        expect(wrapper.contains(".fa-mail-forward")).toBeTruthy();
    });

    it(`Given user is document reader
        When we display the drop zone
        Then user should have an error message`, () => {
        const wrapper = current_folder_drop_zone_factory({
            user_can_dragndrop_in_current_folder: false
        });

        expect(wrapper.contains(".fa-ban")).toBeTruthy();
    });
});
