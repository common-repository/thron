// THRON state : this controller contains your application logic
wp.media.controller.Thron = wp.media.controller.State.extend({

    initialize: function() {
        // this model contains all the relevant data needed for the application
        this.props = new Backbone.Model({
            thron_id: '',
            playerembedtemplate: '',
            thronCollection: null,
            width: '',
            height: ''
        });

        this.props.on('change:thron_id', this.refresh, this);
        this.props.on('change:playerembedtemplate', this.refresh, this);
        this.props.on('change:resizingMode', this.refresh, this);

        // fixed -> o altezza o larghezza
        // responsive -> solo larghezza in percentuale

        this.props.on('change:width', this.refresh, this);
        this.props.on('change:height', this.refresh, this);
    },

    // called each time the model changes
    refresh: function() {
        // update the toolbar
        this.frame.toolbar.get().refresh();
    },

    // called when the toolbar button is clicked
    insertCodeEmbed: function() {
        var playerembedtemplate = this.props.get('playerembedtemplate');
        var thron_id = this.props.get('thron_id');
        var width = this.props.get('width');
        var height = this.props.get('height');
        var embedType = this.props.get('embedType');
        var aspectRatio = this.props.get('aspectRatio') * 100;

        /**
         * Caica la lista dei template
         */
        this.api = THRON.init({
            clientId: myAjax.thron_clientId,
            appId: myAjax.thron_appId,
            appKey: myAjax.thron_appKey
        }).then(function() {
            THRON.callApi(
                'POST',
                '/xcontents/resources/playerembedcode/insert/' + myAjax.thron_clientId, {
                    body: {
                        'skipPkeyCreation': true,
                        'source': {
                            'id': thron_id,
                            'entityType': 'CONTENT'
                        },
                        'value': {
                            'name': 'Name',
                            'template': {
                                'templateId': playerembedtemplate,
                                'templateType': 'CUSTOM'
                            },
                            'enabled': true
                        }
                    }
                }).then(function(response) {
                // console.log('test');
                jQuery('#select_template').html('');

                var template = _.template('[thron contentID="<%= thron_id %>" embedCodeId="<%= embedCodeId %>" width="<%= width %>" height="<%= height %>" embedType="<%= embedType %>" aspectRatio="<%= aspectRatio %>"]'),

                    output = template({
                        thron_id: thron_id,
                        embedCodeId: response.item.id,
                        embedType: embedType,
                        width: width,
                        height: height,
                        aspectRatio: aspectRatio
                    });
                send_to_editor(output);
            });
        });
    }
});

// THRON toolbar : contains the buttons at the bottom
wp.media.view.Toolbar.Thron = wp.media.view.Toolbar.extend({
    initialize: function() {
        _.defaults(this.options, {
            event: 'thron_event',
            close: false,
            items: {
                thron_event: {
                    text: wp.media.view.l10n.THRONActionButton, // added via 'media_view_strings' filter,
                    style: 'primary',
                    priority: 80,
                    requires: false,
                    click: this.insertAction
                }
            }
        });

        wp.media.view.Toolbar.prototype.initialize.apply(this, arguments);
    },

    // called each time the model changes
    refresh: function() {
        // you can modify the toolbar behaviour in response to user actions here
        // disable the button if there is no custom data
        var thron_id = this.controller.state().props.get('thron_id');
        this.get('thron_event').model.set('disabled', !thron_id);

        // call the parent refresh
        wp.media.view.Toolbar.prototype.refresh.apply(this, arguments);
    },

    // triggered when the button is clicked
    insertAction: function() {
        this.controller.state().insertCodeEmbed();
    }
});

// THRON content : this view contains the main panel UI
wp.media.view.Thron = wp.media.View.extend({
    className: 'media-thron',

    // bind view events
    events: {
        'input': 'thron_update',
        'keyup': 'thron_update',
        'change': 'thron_update',
        'click': 'thron_update'
    },

    api: null,

    initialize: function() {
        // create an input
        this.input = this.make();

        this.model.set('thronCollection', new Backbone.Collection());
        this.model.set('embedType', 'responsive');


        // insert it in the view
        this.$el.append(this.input);

        // re-render the view when the model changes
        this.model.on('change:thron_id', this.render, this);

        var t = this;

        /**
         * Download the list of templates
         */
        this.api = THRON.init({
            clientId: myAjax.thron_clientId,
            appId: myAjax.thron_appId,
            appKey: myAjax.thron_appKey
        }).then(function() {

            THRON.callApi(
                'POST',
                '/xcontents/resources/playerembedtemplate/list/' + myAjax.thron_clientId, {
                    body: {
                        'criteria': {
                            'text': ''
                        },
                        'offset': 0,
                        'options': {
                            'returnRoles': false,
                            'returnValues': false
                        }
                    }
                }).then(function(response) {
                jQuery('#select_template').html('').append(' <option value="">' + myAjax.selecttemplate + '</option>');

                _.each(response.items, function(template) {
                    selected = '';

                    if (myAjax.thron_playerTemplates == template.id) {
                        selected = 'selected';
                        t.model.set('playerembedtemplate', template.id);
                    }

                    jQuery('#select_template').append('<option value="' + template.id + '" selected >' + template.name + '</option>');
                })
            });
        });

        jQuery('body').addClass('wsthron');
        jQuery('.media-menu-item').not('.media-menu-item.active').click(function() {
            jQuery('body').removeClass('wsthron');
        });

        setTimeout(function() {
            jQuery('#thron_list').on('scroll', function() {
                if (jQuery(this).scrollTop() + jQuery(this).innerHeight() >= jQuery(this)[0].scrollHeight) {
                    t.thron_search(true);
                }
            });
        }, 3000);

        // mostra i primi risultati
        this.thron_search();
    },

    render: function() {
        this.input.value = this.model.get('thron_id');
        return this;
    },

    make: function() {

        var folder = '';
        var tags = '';

        if ('on' == myAjax.thron_enable_features_search) {
            if (myAjax.tags.length > 0) {
                _.each(myAjax.tags, function(value) {
                    if (myAjax.tags.length > 0) {

                        tags += '<select id="tag-' + value.id + '" class="attachment-filters">';
                        tags += '<option value="">All ' + value.name + '</option>';

                        _.each(value.list, function(value2) {
                            tags += '<option value="' + value2.id + ' ">';
                            tags += value2.name;
                            tags += '</option>';
                        });
                        tags += '</select>';
                    }
                })
            }
        }

        if ((typeof myAjax.folders != 'undefined') & (myAjax.folders != null)) {
            if (myAjax.folders.length > 0) {
                folder += '<select id="folders" class="attachment-filters">';
                folder += '<option value="">' + myAjax.allfolders + '</option>';

                _.each(myAjax.folders, function(value) {
                    folder += '<option id="' + value.id + ' ">';
                    folder += value.name
                    folder += '</option>';
                })
                folder += '</select>';
            }
        }

        return '<div id="thron_content">' +
            '   <div class="media-toolbar" id="thron_toolbar">' +
            '       <div class="media-toolbar-secondary">' +
            '           <div class="media-toolbar-secondary__label"><label class="media-search-input-label">Filters</label></div>' +
            '           <select id="mediaType" class="attachment-filters">' +
            '               <option value="">' + myAjax.allcontent + '</option>' +
            '               <option value="VIDEO">' + myAjax.videos +'</option>' +
            '               <option value="AUDIO">' + myAjax.audio +'</option>' +
            '               <option value="IMAGE">' + myAjax.images + '</option>' +
            '               <option value="OTHER">' + myAjax.documents +'</option>' +
            '               <option value="PLAYLIST">' + myAjax.playlist +'</option>' +
            '               <option value="URL">' + myAjax.url +'</option>' +
            '               <option value="PAGELET">' + myAjax.pagelet +'</option>' +
            '           </select>' +
                        folder +
                        tags +
            '       </div>' +
            '       <div class="media-toolbar-primary search-form">' +
            '           <label for="media-search-input" class="media-search-input-label">Cerca</label>' +
            '           <input type="search" placeholder="Cerca" class="search" val="{data.value}" />' +
            '       </div>' +
            '   </div>' +
            '   <div class="td-content-grid" id="thron_list"></div>' +
            '</div>' +
            '<div class="media-sidebar visible" id="thron_bar">' +
            '   <div id="thron_attachments_preview"></div>' +
            '   <div id="thron_playerembedtemplate_preview"></div>' +
            '   <div class="media-uploader-status">' +
            '   <div>' +
            '       <label for="select_template">' + myAjax.playertemplate + '</label>' +
            '       <select id="select_template" name="select_template"></select>' +
            '   </div>' +
            '   <div>' +
            '       <label for="embedType">' + myAjax.embedtype + '</label>' +
            '       <div class=\"input_container\"><input type="radio" val="responsive" id="responsive" name="embedType" checked /> Responsive<br \></div>' +
            '       <div class=\"input_container\"><input type="radio"  val="fixed" id="fixed" name="embedType" /> ' + myAjax.fixedsize + '</div>' +
            '   </div>' +
            '   <div class=\"sidebar_img-dimension\">' +
            '       <input type="number" id="thron-player-width" val="" placeholder="' + myAjax.widthpx + '"/>' +
            '       <div class=\"dimension_separator\"> X </div>' +
            '       <input type="number" id="thron-player-height" val="" placeholder="' + myAjax.heightpx + '"/>' +
            '   </div>' +
            '</div>';
    },

    thron_update: function(event) {
        var t = this;

        var thron_id = event.target.getAttribute('data-id');

        if (thron_id) {
            this.model.set('thron_id', thron_id);

            /**
             * Request content detils
             */
            THRON.callApi(
                    'GET',
                    '/xcontents/resources/delivery/getContentDetail?clientId=' + myAjax.thron_clientId + '&xcontentId=' + thron_id
                )
                .then(function(response) {

                    deliverySize = response.content.deliverySize.aspectRatio.split(':');

                    t.model.set('aspectRatio', deliverySize[1] / deliverySize[0]);

                });

            jQuery('#thron_attachments_preview').html('<h2>' + myAjax.contentdetails + '</h2><div class="attachment-info"><div class="thumbnail thumbnail-image"><img src="https://' + myAjax.thron_clientId + '-view.thron.com/api/xcontents/resources/delivery/getThumbnail/' + myAjax.thron_clientId + '/150x150/' + thron_id + '" ></div></div>');
        }

        if ('input' == event.type) {
            /**
             * Gestisce il tipo di embed:
             *  * responsive
             *  * fixed
             */
            if ('embedType' == event.target.name) {

                this.model.set('embedType', event.target.id);
            }

            /**
             * Gestisce l'altezza e la larghezza dell'embed
             */
            if ('thron-player-width' == event.target.id) {
                this.model.set('width', event.target.value);

                var aspectRatio = this.model.get('aspectRatio');
                if (aspectRatio) {
                    this.model.set('height', event.target.value * aspectRatio);

                    jQuery('#thron-player-height').val(event.target.value * aspectRatio)
                }
            }

            if ('thron-player-height' == event.target.id) {
                this.model.set('height', event.target.value);

                var aspectRatio = this.model.get('aspectRatio');
                if (aspectRatio) {
                    this.model.set('width', event.target.value * aspectRatio);

                    jQuery('#thron-player-width').val(event.target.value / aspectRatio)
                }
            }

            /**
             * Gestisce la folders
             */
            if ('folders' == event.target.id) {
                this.model.set('folders', event.target.options[event.target.selectedIndex].id.trim());
                this.model.set('pageToken', null);

                jQuery('#thron_list').html('');
                this.thron_search()
            }

            /**
             * Gestisce i tags
             */
            if ('tag-' == event.target.id.substring(0, 4)) {
                var tags = this.model.get('tags') || new Array();
                var new_tags = new Array();

                var id = event.target.id.substring(4).split(';');

                _.each(tags, function(tag) {
                    if (id[1] != tag.parent) {
                        new_tags.push(tag);
                    }
                })

                new_tags.push({
                    id: event.target.options[event.target.selectedIndex].value.trim(),
                    classificationID: id[0],
                    parent: id[1]
                });

                this.model.set('tags', new_tags);
                this.model.set('pageToken', null);

                jQuery('#thron_list').html('');
                this.thron_search();
            }

            /**
             * Gestisce tipi di file
             */
            if ('mediaType' == event.target.id) {

                this.model.set('mediaType', event.target.value);
                this.model.set('pageToken', null);

                jQuery('#thron_list').html('');
                this.thron_search();
            }
        }

        if ('search' == event.target.type) {
            this.model.set('search', event.target.value);
            this.model.set('pageToken', null);

            jQuery('#thron_list').html('');
            this.thron_search();
        }

        if ('select-one' == event.target.type) {
            this.model.set('playerembedtemplate', event.target.value);
        }
    },

    requestId: -1,

    thron_search: function(append) {

        var t = this;
        var pageToken = this.model.get('pageToken');

        var thronCollection = this.model.get('thronCollection');
        var search = this.model.get('search');
        var folders = this.model.get('folders');
        var tags = this.model.get('tags');
        var mediaType = this.model.get('mediaType');

        this.api = THRON.init({
            clientId: myAjax.thron_clientId,
            appId: myAjax.thron_appId,
            appKey: myAjax.thron_appKey
        }).then(function() {

            var body = {};
            body.criteria = {};

            if (search) {
                body.criteria.lemma = {};
                body.criteria.lemma.text = search;
                body.criteria.lemma.textMatch = 'any_word_match';
            }

            if (folders) {
                body.criteria.linkedCategories = {};
                body.criteria.linkedCategories.haveAtLeastOne = new Array({
                    id: folders,
                    cascade: false
                });
            }

            if (tags) {
                body.criteria.itag = {};
                body.criteria.itag.haveAll = [];

                _.each(tags, function(tag) {
                    if (tag.id != '') {
                        body.criteria.itag.haveAll.push({
                            id: tag.id,
                            classificationId: tag.classificationID,
                            cascade: false
                        });
                    }

                });
            }

            if (mediaType) {
                body.criteria.contentType = new Array(mediaType);
            }

            if (pageToken) {
                body.pageToken = pageToken;
            }

            body.responseOptions = new Object();
            body.responseOptions.resultsPageSize = 50;
            body.responseOptions.returnDetailsFields = new Array('locales', 'source');
            
            var currentRequestId = ++this.requestId;

            THRON.callApi(
                'POST',
                '/xcontents/resources/content/search/' + myAjax.thron_clientId, { body: body }
            ).then(function(response) {
                // console.log(currentRequestId, this.requestId);
                if (currentRequestId !== this.requestId) {
                    return;
                }

                t.model.set('pageToken', response.nextPageToken);

                var template = _.template(
                    '<div class="content">' +
                    '   <picture>'+
                    '       <img class="thumb_img" data-id="<%- id %>" src="https://<%- clientId %>-view.thron.com/api/xcontents/resources/delivery/getThumbnail/<%- clientId %>/200x150/<%- id %>?scalemode=auto" \>' +
                    '   </picture>' +
                    '   <div class="label">' +
                    '       <span class="thron-type" data-value="<%- thronType %>"></span>' +
                    '       <span class="thron-extension"><%- extension %></span>' +
                    '       <span class="thron-label"><%- value %></span>' +
                    '   </div>' +
                    '</div>'
                );

                var output = '';

                if (response.items.length === 0 && !append) {
                    output += '<div class="empty-results">' + i18n.__('No result for this search', 'thron') + '</div>';
                }

                _.each(response.items, function(value) {

                    /**
                     * Aggiunge il content nella collection
                     */
                    thronCollection.add(value);

                    output += template({
                        id: value.id,
                        value: value.details.locales[0].name,
                        clientId: myAjax.thron_clientId,
                        thronType: value.contentType,
                        extension: typeof value.details.source !== 'undefined' ? value.details.source.extension : value.contentType
                    });
                });

                if (append) {
                    jQuery('#thron_list').append(output);
                } else {
                    jQuery('#thron_list').html(output);
                }

                jQuery('.content').click(function() {
                    jQuery.each(jQuery('.content'), function() {
                        jQuery(this).removeClass('selected');
                    });
                    jQuery(this).toggleClass('selected');
                    if (jQuery('#select_template').val() == '') {
                        setTimeout(function() {
                            jQuery('.media-button-thron_event').prop('disabled', true);
                        }, 300)
                    } else {
                        setTimeout(function() {
                            jQuery('.media-button-thron_event').prop('disabled', false);
                        }, 300)
                    }
                });

                jQuery('#select_template').change(function() {
                    if (jQuery('#select_template').val() == '') {
                        setTimeout(function() {
                            jQuery('.media-button-thron_event').prop('disabled', true);
                        }, 300)
                    } else {
                        setTimeout(function() {
                            jQuery('.media-button-thron_event').prop('disabled', false);
                        }, 300)
                    }
                });

                jQuery('.thron-type, .thron-extension, .thron-label').click(function() {
                    jQuery(this).closest('.content').find('img').click();
                });

                jQuery('.media-toolbar-secondary select').change(function() {
                    setTimeout(function() {
                        if (jQuery('#thron_list .content').length < 8) jQuery('.wsthron #thron_list .content').css('flex-grow', 'initial');
                    }, 300);
                });
            }.bind(this));

        }.bind(this));
    }
});

// supersede the default MediaFrame.Post view
var oldMediaFrame = wp.media.view.MediaFrame.Post;
wp.media.view.MediaFrame.Post = oldMediaFrame.extend({

    initialize: function() {
        oldMediaFrame.prototype.initialize.apply(this, arguments);

        if (this.options.state !== 'insert')
            return;

        this.states.add([
            new wp.media.controller.Thron({
                id: 'my-action',
                menu: 'default', // menu event = menu:render:default
                content: 'Thron',
                title: wp.media.view.l10n.THRONMenuTitle, // added via 'media_view_strings' filter
                priority: 200,
                toolbar: 'main-my-action', // toolbar event = toolbar:create:main-my-action
                type: 'link'
            })
        ]);

        this.on('content:render:Thron', this.thronAPP, this);
        this.on('toolbar:create:main-my-action', this.createTHRONToolbar, this);
        // this.on('toolbar:render:main-my-action', this.renderTHRONToolbar, this);
    },

    createTHRONToolbar: function(toolbar) {
        toolbar.view = new wp.media.view.Toolbar.Thron({
            controller: this
        });
    },

    thronAPP: function() {

        // this view has no router
        this.$el.addClass('hide-router');

        // THRON content view
        var view = new wp.media.view.Thron({
            controller: this,
            model: this.state().props
        });

        this.content.set(view);
    }

});
