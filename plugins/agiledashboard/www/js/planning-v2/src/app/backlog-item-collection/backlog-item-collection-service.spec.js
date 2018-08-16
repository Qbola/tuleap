import angular from "angular";
import "angular-mocks";

import collection_module from "./backlog-item-collection.js";

describe("BacklogItemCollectionService -", () => {
    let $q, BacklogItemCollectionService, BacklogItemService;

    beforeEach(() => {
        angular.mock.module(collection_module);

        angular.mock.inject(function(
            _$q_,
            _$rootScope_,
            _BacklogItemCollectionService_,
            _BacklogItemService_
        ) {
            $q = _$q_;
            BacklogItemCollectionService = _BacklogItemCollectionService_;
            BacklogItemService = _BacklogItemService_;
        });

        spyOn(BacklogItemService, "getBacklogItem");

        installPromiseMatchers();
    });

    describe("refreshBacklogItem() -", () => {
        describe("Given a backlog item's id and given that this item existed in the item collection", () => {
            let initial_item;

            beforeEach(() => {
                initial_item = {
                    id: 7088,
                    background_color_name: "",
                    card_fields: [],
                    children: {
                        data: [],
                        collapsed: true,
                        loaded: true
                    },
                    has_children: false,
                    initial_effort: 8,
                    remaining_effort: 7,
                    label: "hexapod",
                    status: "Review",
                    updating: false
                };

                BacklogItemCollectionService.items = {
                    7088: initial_item
                };
            });

            it("when I refresh it, then a promise will be resolved and the item will be fetched from the server and updated in the item collection", () => {
                const updated_item = {
                    backlog_item: {
                        id: 7088,
                        background_color_name: "glossopalatine_sophic",
                        card_fields: [
                            {
                                field_id: 35,
                                label: "Remaining Story Points",
                                type: "float",
                                value: 1.5
                            }
                        ],
                        has_children: true,
                        initial_effort: 6,
                        remaining_effort: 3,
                        label: "unspeedy",
                        status: "Closed",
                        parent: {
                            id: 504,
                            label: "pretangible"
                        }
                    }
                };

                BacklogItemService.getBacklogItem.and.returnValue($q.when(updated_item));

                const promise = BacklogItemCollectionService.refreshBacklogItem(7088);

                expect(BacklogItemCollectionService.items[7088].updating).toBeTruthy();

                expect(promise).toBeResolved();
                expect(BacklogItemService.getBacklogItem).toHaveBeenCalledWith(7088);
                expect(BacklogItemCollectionService.items[7088]).toEqual({
                    id: 7088,
                    background_color_name: "glossopalatine_sophic",
                    card_fields: [
                        {
                            field_id: 35,
                            label: "Remaining Story Points",
                            type: "float",
                            value: 1.5
                        }
                    ],
                    children: {
                        data: [],
                        collapsed: true,
                        loaded: true
                    },
                    has_children: true,
                    initial_effort: 6,
                    remaining_effort: 3,
                    label: "unspeedy",
                    status: "Closed",
                    parent: {
                        id: 504,
                        label: "pretangible"
                    },
                    updating: false,
                    updated: true
                });
            });
        });
    });
});
