(function () {

    var dataStore = {
        tags: null,
    };

    /**
     * Create a new MediaLibraryTaxonomyFilter we later will instantiate
     */
    var MediaLibrarySourceFilter = wp.media.view.AttachmentFilters.extend({
        id: 'media-attachment-thron-source-filter',

        createFilters: function () {

            var filters = {};

            var props = {};
            props['thron_source'] = 'thron';

            filters.all = {
                text: 'THRON',
                props: props,
                priority: 10
            };

            var props = {};
            props['thron_source'] = 'local';
            filters['local'] = {
                text: 'Local files',
                props: props
            };


            this.filters = filters;
        }
    });


    /**
     * Create a new MediaLibraryTaxonomyFilter we later will instantiate
     */
    var MediaLibraryTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
        id: 'media-attachment-thron-tags-filter',
        className : 'attachment-filters attachment-filters-tag',

        createFilters: function () {

            var filters = {};

            _.each(dataStore.tags.list || {}, function (value, index) {

                var props = {};
                props['thron_tags_' + dataStore.tags.id] = value.id

                filters[value.id] = {
                    text: value.name,
                    props: props
                };
            });

            var props = {};
            props['thron_tags_' + dataStore.tags.id] = '';

            filters.all = {
                text: ThronTagsList.lang.all + ' ' + dataStore.tags.name,
                props: props,
                priority: 10
            };

            this.filters = filters;
        }
    });

    /**
     * Create a new MediaLibraryTaxonomyFilter we later will instantiate
     */
    var MediaLibraryFoldersFilter = wp.media.view.AttachmentFilters.extend({
        id: 'media-attachment-thron-folders-filter',

        createFilters: function () {

            var filters = {};

            var props = {};
            props['thron_categories'] = '';

            filters.all = {
                text: ThronTagsList.lang.allFolders,
                props: props,
                priority: 10
            };

            _.each(ThronTagsList.folders || {}, function (value, index) {

                var props = {};
                props['thron_categories'] = value.id;

                filters[value.id] = {
                    text: value.name,
                    props: props
                };

            });

            this.filters = filters;
        }
    });

    /**
     * Extend and override wp.media.view.AttachmentsBrowser to include our new filter
     */
    var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
        createToolbar: function () {
            // Make sure to load the original toolbar
            AttachmentsBrowser.prototype.createToolbar.call(this);

            var t = this;

            /**
             * Viene creato il filtro per la sorgente
             */
            t.toolbar.set('MediaLibrarySourceFilter', new MediaLibrarySourceFilter({
                controller: t.controller,
                model: t.collection.props,
                priority: -150
            }).render());

            /**
             * Viene creato il filtro per le cartelle
             */
            t.toolbar.set('MediaLibraryFoldersFilter', new MediaLibraryFoldersFilter({
                controller: t.controller,
                model: t.collection.props,
                priority: -75
            }).render());

            /**
             * Vengono creati i filtri per ogni tag
             */

            if ('on' == myAjax.thron_enable_features_search) {
                _.each(ThronTagsList.tags || {}, function (value, index) {

                    dataStore.tags = value;

                    t.toolbar.set('MediaLibraryTaxonomyFilter' + value.id, new MediaLibraryTaxonomyFilter({
                        controller: t.controller,
                        model: t.collection.props,
                        priority: -75
                    }).render());
                });
            }
        }
    });
})()