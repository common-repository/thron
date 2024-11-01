jQuery(document).ready(function($) {

    /**
     * Colloca il div thron-sidebar come sidebar nella media form
     */

    if (wp.media) {


        //wp.media.view.Attachment.prototype.template = wp.media.template('tmpl-attachment-details-two-column-custom');
        //wp.media.view.Attachment.Details.TwoColumn.prototype.template = wp.media.template('tmpl-attachment-details-two-column-custom');

        //console.log(wp.media.view.Attachment.Details.TwoColumn.prototype.template)

        //jQuery(document).ready( function($) {
        //console.log(wp.media.template('attachment-details-two-column-custom'))
        /*if (typeof wp.media.view.Attachment != 'undefined') {
            wp.media.view.Attachment.Details.TwoColumn.prototype.template = wp.media.template('attachment-details-two-column-custom');
        }*/
        //});

        // Ensure that the Modal is ready.
        wp.media.view.Modal.prototype.on("all", function(e) {
            //console.log(e); 
        });


        wp.media.view.Modal.prototype.on("close", function() {

            console.log('Inizio salvataggio...');

            var state = wp.media.frame.state();

            console.log('Stato: ' + state);


            var selection = wp.media.frame.state().get('selection');

            var collection = wp.media.frame.content.get().collection;

            console.log('Stato: ' + state.id);

            switch (state.id) {
                case "library":
                    /**
                     * image
                     */
                    selection.each(function(attachment) {

                        var element = collection.get(attachment.id);

                        console.log(attachment)

                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: myAjax.ajaxurl,
                            data: { action: "thron_file_upload", 'thron_id': attachment.id },
                            async: false,
                            success: function(response) {
                                if (response.success == true) {

                                    element.set({ id: response.data.ID });
                                    element.set({ url: response.data.url });

                                    collection.set({ element }, { remove: false })
                                } else {}
                            }
                        });
                    });
                    break;
                case "featured-image":
                    /**
                     * image
                     */
                    selection.each(function(attachment) {

                        var element = collection.get(attachment.id);

                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: myAjax.ajaxurl,
                            data: { action: "thron_file_upload", 'thron_id': attachment.id },
                            async: false,
                            success: function(response) {
                                if (response.success == true) {

                                    element.set({ id: response.data.ID });
                                    element.set({ url: response.data.url });

                                    collection.set({ element }, { remove: false })
                                } else {}
                            }
                        });
                    });
                    break;
                case "insert":
                    /**
                     * image
                     */

                    selection.each(function(attachment) {

                        var element = collection.get(attachment.id);


                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: myAjax.ajaxurl,
                            data: {
                                action: "thron_file_upload",
                                'thron_id': attachment.id
                            },
                            async: false,
                            success: function(response) {
                                if (response.success == true) {

                                    element.set({ id: response.data.ID });
                                    element.set({ url: response.data.url });

                                    collection.set({ element }, { remove: false });
                                } else {}
                            }
                        });
                    });
                    break;
                case "gallery-edit":
                    /**
                     * Gallery
                     */

                    collection.each(function(attachment) {

                        console.log(attachment);

                        var element = collection.get(attachment.id);

                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: myAjax.ajaxurl,
                            data: { action: "thron_file_upload", 'thron_id': attachment.id },
                            async: false,
                            success: function(response) {
                                if (response.success == true) {

                                    element.set({ id: response.data.ID });
                                    element.set({ url: response.data.url });

                                    collection.set({ element }, { remove: false })
                                } else {}
                            }
                        });
                    });

                    break;
            }

            console.log('Fine del salvataggio...');

        });

    }

});