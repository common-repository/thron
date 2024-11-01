(function(blocks, blockEditor, i18n, element, components, _) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var DropDownMenu = components.DropdownMenu;
    var MenuItem = components.MenuItem;
    var PanelBody = components.PanelBody;
    var thePlayer = [];
    var destroy = false;
    var BlockControls = blockEditor.BlockControls;

    const { select } = wp.data;


    const ManualPos = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'manual_pos',
            'data-name': 'manual pos',
            className: 'cls-1',
            d: 'M263.5,32V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm4.291-11.4v6.4h0.8V39.008L254.02,36.53l1.666,4.97H256.3l1.674-4.988-0.074,2.5V41.5h0.8V35.1h-1.037L256,40.322l-1.67-5.221h-1.037Z',
            transform: 'translate(-244 -27)'
        })
    );

    const ManualNeg = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'manual_neg',
            'data-name': 'manual neg',
            className: 'cls-1',
            d: 'M246,27h20a2,2,0,0,1,2,2V49a2,2,0,0,1-2,2H246a2,2,0,0,1-2-2V29A2,2,0,0,1,246,27Zm17.5,5V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm4.291-11.4v6.4h0.8V39.008L254.02,36.53l1.666,4.97H256.3l1.674-4.988-0.074,2.5V41.5h0.8V35.1h-1.037L256,40.322l-1.67-5.221h-1.037Z',
            transform: 'translate(-244 -27)'
        })
    );


    const AutoPos = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd; fill: #1e1e1e; }'
            )
        ),
        el('path', {
            id: 'auto_pos',
            'data-name': 'auto pos',
            className: 'cls-1',
            d: 'M263.5,32V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm8.586-5h0.827l-2.066-6.4h-0.69L253.6,41.5h0.826l0.5-1.674h2.162ZM256,36.227l0.875,2.909H255.13Z',
            transform: 'translate(-244 -27)'
        })
    );

    const AutoNeg = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'auto_neg',
            'data-name': 'auto neg',
            className: 'cls-1',
            d: 'M246,27h20a2,2,0,0,1,2,2V49a2,2,0,0,1-2,2H246a2,2,0,0,1-2-2V29A2,2,0,0,1,246,27Zm17.5,5V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm8.586-5h0.827l-2.066-6.4h-0.69L253.6,41.5h0.826l0.5-1.674h2.162ZM256,36.227l0.875,2.909H255.13Z',
            transform: 'translate(-244 -27)'
        })
    );

    const CenteredPos = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'centered_pos',
            'data-name': 'centered pos',
            className: 'cls-1',
            d: 'M263.5,32V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm8.338-7.035A1.96,1.96,0,0,1,257,40.586a1.154,1.154,0,0,1-.905.312,1.061,1.061,0,0,1-.956-0.519,2.8,2.8,0,0,1-.327-1.49V37.716a2.654,2.654,0,0,1,.351-1.5,1.149,1.149,0,0,1,1.007-.512,1.034,1.034,0,0,1,.844.334,1.981,1.981,0,0,1,.329,1.116h0.813a2.427,2.427,0,0,0-.551-1.586,1.85,1.85,0,0,0-1.435-.554,1.928,1.928,0,0,0-1.589.725,3.066,3.066,0,0,0-.582,1.973v1.16a3.159,3.159,0,0,0,.567,1.984,1.833,1.833,0,0,0,1.529.732,1.958,1.958,0,0,0,1.483-.547,2.371,2.371,0,0,0,.578-1.575h-0.813Z',
            transform: 'translate(-244 -27)'
        })
    );

    const CenteredNeg = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'centered_neg',
            'data-name': 'centered neg',
            className: 'cls-1',
            d: 'M246,27h20a2,2,0,0,1,2,2V49a2,2,0,0,1-2,2H246a2,2,0,0,1-2-2V29A2,2,0,0,1,246,27Zm17.5,5V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm8.338-7.035A1.96,1.96,0,0,1,257,40.586a1.154,1.154,0,0,1-.905.312,1.061,1.061,0,0,1-.956-0.519,2.8,2.8,0,0,1-.327-1.49V37.716a2.654,2.654,0,0,1,.351-1.5,1.149,1.149,0,0,1,1.007-.512,1.034,1.034,0,0,1,.844.334,1.981,1.981,0,0,1,.329,1.116h0.813a2.427,2.427,0,0,0-.551-1.586,1.85,1.85,0,0,0-1.435-.554,1.928,1.928,0,0,0-1.589.725,3.066,3.066,0,0,0-.582,1.973v1.16a3.159,3.159,0,0,0,.567,1.984,1.833,1.833,0,0,0,1.529.732,1.958,1.958,0,0,0,1.483-.547,2.371,2.371,0,0,0,.578-1.575h-0.813Z',
            transform: 'translate(-244 -27)'
        })
    );

    const ProductPos = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'product_pos',
            'data-name': 'product pos',
            className: 'cls-1',
            d: 'M263.5,32V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm7.292-7.5a1.878,1.878,0,0,0,1.369-.516,2.278,2.278,0,0,0-.024-2.843,1.846,1.846,0,0,0-1.4-.534H254.2v6.4H255V39h1.292ZM255,35.792h1.24a1,1,0,0,1,.808.348,1.385,1.385,0,0,1,.3.924,1.343,1.343,0,0,1-.286.92,1.042,1.042,0,0,1-.822.321H255V35.792Z',
            transform: 'translate(-244 -27)'
        })
    );

    const ProductNeg = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
        el('defs',
            el('style', {},
                '.cls-1 {fill-rule: evenodd;}'
            )
        ),
        el('path', {
            id: 'product_neg',
            'data-name': 'product neg',
            className: 'cls-1',
            d: 'M246,27h20a2,2,0,0,1,2,2V49a2,2,0,0,1-2,2H246a2,2,0,0,1-2-2V29A2,2,0,0,1,246,27Zm17.5,5V43H265V32a2.006,2.006,0,0,0-2-2H252v1.5h11A0.472,0.472,0,0,1,263.5,32ZM249,46.5a0.472,0.472,0,0,1-.5-0.5V27H247v3h-3v1.5h3V46a2.006,2.006,0,0,0,2,2h14.5v3H265V48h3V46.5H249Zm7.292-7.5a1.878,1.878,0,0,0,1.369-.516,2.278,2.278,0,0,0-.024-2.843,1.846,1.846,0,0,0-1.4-.534H254.2v6.4H255V39h1.292ZM255,35.792h1.24a1,1,0,0,1,.808.348,1.385,1.385,0,0,1,.3.924,1.343,1.343,0,0,1-.286.92,1.042,1.042,0,0,1-.822.321H255V35.792Z',
            transform: 'translate(-244 -27)'
        })
    );


    const THRONLogo = el('svg', { width: 20, height: 20, viewBox: '80 80 140 140' },
        el('path', {
            fill: '#F39900',
            d: 'M116.52,117.58c-8.5,8.89-12.84,19.69-12.84,32.38c0,13.03,4.35,23.95,13.06,32.76\n' +
                '\t\tc8.65,8.86,19.36,13.29,32.12,13.29c12.71,0,23.45-4.43,32.2-13.29c8.76-8.76,13.13-19.57,13.13-32.44\n' +
                '\t\tc0-13.51-4.53-24.61-13.61-33.31c-8.98-8.66-19.33-13.01-31.08-13.06V86.66c15.83,0,30,5.46,42.55,16.38\n' +
                '\t\tc13.82,12.08,20.73,27.9,20.73,47.47c0,17.19-6.3,31.96-18.91,44.31c-12.55,12.34-27.64,18.51-45.26,18.51\n' +
                '\t\tc-17.41,0-32.33-6.23-44.78-18.67c-12.5-12.45-18.75-27.4-18.75-44.86c0-17.57,6.34-32.53,18.89-44.76L116.52,117.58z'
        }),
        el('path', {
            fill: '#848587',
            d: 'M103.97,105.04c12.64-12.24,27.76-18.36,45.54-18.38v17.25c-13,0-23.81,4.43-32.62,13.29c-0.12,0.13-0.17,0.25-0.37,0.38L103.97,105.04z'
        })
    );

    const CropIcon = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24', role: 'img', focusable: 'false' },
        el('path', {
            d: 'M17.5 7v8H19V7c0-1.1-.9-2-2-2H9v1.5h8c.3 0 .5.2.5.5zM7 17.5c-.3 0-.5-.2-.5-.5V1H5v4H1v1.5h4V17c0 1.1.9 2 2 2h10.5v4H19v-4h4v-1.5H7z'
        })
    );

    const initialCropIcon = function(props) {
        switch (props.attributes.scalemode) {
            case 'auto':
                return AutoPos;
            case 'centered':
                return CenteredPos;
            case 'product':
                return ProductPos;
            case 'manual':
                return ManualPos;
            default:
                return CropIcon;
        }
    }

    function cropOps(args, props) {
        var transform = document.querySelector('#thron-preview-id-' + props.clientId + ' .th-main-container .th-media-container .th-image-player').style.transform;

        var numbers = transform.split('matrix(')[1].split(')')[0].split(',').map(function(item) {
            return parseInt(item);
        });
        var translateX = numbers[4];
        var translateY = numbers[5];
        var scale = numbers[0];

        // get size of some elements (this is needed to calculate crop params for RTIE from matrix information)
        var imageSize = document.querySelector('#thron-preview-id-' + props.clientId + ' .th-main-container .th-media-container .th-image-player img').getBoundingClientRect();
        var playerSize = document.querySelector('#thron-preview-id-' + props.clientId).getBoundingClientRect();

        // we also need the size of original image, do getcontentdetails again (player does not expose this info)
        var url = 'https://{clientId}-view.thron.com/api/xcontents/resources/delivery/getContentDetail?clientId={clientId}&xcontentId={xcontentId}&templateId=CE1&pkey={sessId}';
        url = url.replace(/{clientId}/g, args.clientId);
        url = url.replace('{xcontentId}', props.attributes.contentID);
        url = url.replace('{sessId}', args.pkey);


        return fetch(url, {
                method: 'GET'
            })
            .then(data => data.json())
            .then(contentdetails => {
                var originalHeight = contentdetails.content.deliverySize.maxHeight;
                var originalWidth = contentdetails.content.deliverySize.maxWidth;
                var cropx = (imageSize.width / 2) - Math.min(playerSize.width / 2, imageSize.width / 2) - translateX;
                var cropy = (imageSize.height / 2) - Math.min(playerSize.height / 2, imageSize.height / 2) - translateY;
                var cropw = Math.min(imageSize.right, playerSize.right) - Math.max(imageSize.left, playerSize.left);
                var croph = Math.min(imageSize.bottom, playerSize.bottom) - Math.max(imageSize.top, playerSize.top);
                var originalCropW = cropw;
                var originalCropH = croph;
                cropx = Math.min(imageSize.width, Math.max(0, cropx));
                cropy = Math.min(imageSize.height, Math.max(0, cropy));
                cropw = Math.min(imageSize.width, Math.max(0, cropw));
                croph = Math.min(imageSize.height, Math.max(0, croph));
                cropx = cropx * originalWidth / imageSize.width;
                cropy = cropy * originalHeight / imageSize.height;
                cropw = cropw * originalWidth / imageSize.width;
                croph = croph * originalHeight / imageSize.height;
                return {
                    'cropx': cropx,
                    'cropy': cropy,
                    'cropw': cropw,
                    'croph': croph
                }
            })
    }

    function wsRenderPlayer(args, props, contentID, embedCode) {
        var rtie = null

        var template = args.templateList.filter(function(value) {
            if (value.id == props.attributes.templateID) {
                return value
            }
        })
        var tplValues = template[0].values.filter(function(v) {
            if (v.name === 'TH-CSS-SKIN_CUSTOM') return v.value
        })

        tplValue = tplValues.length ? tplValues[0].value : 'NOVALUE'

        var tplPlugins = {}
        var tplSkins = {}

        if (props.attributes.manualCropSaving) { //default template for manual
            tplPlugins['NOVALUE'] = null
            tplSkins['NOVALUE'] = null
        } else {
            tplPlugins[tplValue] = typeof template[0].jsUrl !== 'undefined' ? { 'src': template[0].jsUrl } : null
            tplSkins[tplValue] = typeof template[0].cssUrl !== 'undefined' ? { 'src': template[0].cssUrl } : null
        }

        if (props.attributes.scalemode == 'manual' &&
            typeof props.attributes.rtieCrop != 'undefined' &&
            props.attributes.rtieCrop != null) {

            rtie = {
                    'cropmode': 'pixel',
                    'quality': props.attributes.quality,
                    'scalemode': props.attributes.scalemode,
                    'cropx': props.attributes.rtieCrop.cropx,
                    'cropy': props.attributes.rtieCrop.cropy,
                    'cropw': props.attributes.rtieCrop.cropw,
                    'croph': props.attributes.rtieCrop.croph,
                    'enhance': 'brightness:' + (props.attributes.brightness + 100) + ',contrast:' + (props.attributes.contrast + 100) + ',sharpness:' + (props.attributes.sharpness + 100) + ',color:' + (props.attributes.color + 100)
                }
                /*props.setAttributes({
                    manualCropSaving: false,
                });*/
        } else {
            rtie = {
                'cropmode': 'pixel',
                'quality': props.attributes.quality,
                'scalemode': props.attributes.scalemode,
                'enhance': 'brightness:' + (props.attributes.brightness + 100) + ',contrast:' + (props.attributes.contrast + 100) + ',sharpness:' + (props.attributes.sharpness + 100) + ',color:' + (props.attributes.color + 100)
            }
        }



        var options = {
            'plugins': tplPlugins,
            'skins': tplSkins,
            'clientId': args.clientId,
            'xcontentId': contentID,
            'sessId': args.pkey,
            /*'embedCodeId': embedCode,*/
            'ignoreUserBehavior': true,
            'rtie': rtie,
            'useCache': false
        };

        var isContainerEmpty = document.getElementById('thron-preview-id-' + props.clientId)?.innerHTML === "";
        if ((typeof thePlayer[props.clientId] !== 'undefined' && destroy) || isContainerEmpty) {
            thePlayer[props.clientId]?.destroy()
            destroy = false;
        }
        thePlayer[props.clientId] = THRONContentExperience('thron-preview-id-' + props.clientId, options);

        if (props.attributes.scalemode == 'manual' && props.attributes.manualCropSaving) {



            // prima di generare il player gestisco la rimozione dei tasti non necessari
            thePlayer[props.clientId].on('beforeInit',
                function(playerInstance) {

                    var schema = window.THRONSchemaHelper.getSchema();
                    //configure the position of the button
                    var elements = window.THRONSchemaHelper.removeElementsById(schema, 'IMAGE', 'captionText', 'shareButton', 'downloadableButton', 'zoomText', 'fullscreenButton');
                    var params = playerInstance.params()
                    params['bars'] = schema
                        //add params
                    playerInstance.params(params);
                }
            );
            // quando il player e pronto inserisco la griglia a video
            thePlayer[props.clientId].on('ready', function(playerInstance) {
                //create div grid
                var mediaContainer = thePlayer[props.clientId].mediaContainer();
                var newNode = document.createElement('div');
                newNode.className = 'th-wrapper-grid';
                //generate box
                for (i = 0; i < 9; i++) {
                    var newNodeGrid = document.createElement('div');
                    newNodeGrid.className = 'th-wrapper-grid-box';
                    newNode.appendChild(newNodeGrid)
                }
                //add div grid
                mediaContainer.appendChild(newNode)
            })
        }
    }


    var debouncedRenderPlayer = _.debounce(function(args, props, contentID, embedCode) {
        wsRenderPlayer(args, props, contentID, embedCode)
    }, 300)

    blocks.registerBlockType('thron/embed-player-block', {
        title: i18n.__('THRON Universal Player', 'thon'),
        description: i18n.__('Insert THRON player', 'thron'),
        icon: THRONLogo,
        category: 'embed',
        supports: {
            align: false,
            alignWide: false
        },
        attributes: {
            initPlayer: {
                type: 'boolean',
                default: true
            },
            dropDownCropIcon: {
                type: 'string',
                default: null
            },
            manualCropSaving: {
                type: 'boolean',
                default: false
            },
            isOpen: {
                type: 'boolean',
                default: false
            },
            searchString: {
                type: 'string',
                default: ''
            },
            searchResult: {
                type: 'array',
                default: []
            },
            tmpContentID: {
                type: 'string',
                default: null
            },
            contentID: {
                type: 'string',
                default: null
            },
            templateID: {
                type: 'string',
                default: args.thron_playerTemplates
            },
            embedCode: {
                type: 'string',
                default: null
            },
            scalemode: {
                type: 'string',
                default: null
            },
            currentScaleMode: {
                type: 'string',
                default: null
            },
            rtieCrop: {
                cropx: {
                    type: 'number',
                    default: 0
                },
                cropy: {
                    type: 'number',
                    default: 0
                },
                cropw: {
                    type: 'number',
                    default: 0
                },
                croph: {
                    type: 'number',
                    default: 0
                }
            },
            quality: {
                type: 'number',
                default: 90
            },
            brightness: {
                type: 'number',
                default: 0
            },
            contrast: {
                type: 'number',
                default: 0
            },
            sharpness: {
                type: 'number',
                default: 0
            },
            color: {
                type: 'number',
                default: 0
            },

            /**
             * Dimension
             */
            embedType: {
                type: 'string',
                default: 'responsive'
            },
            width: {
                type: 'number',
                default: 100
            },
            height: {
                type: 'number',
                default: null
            },
            maxWidth: {
                type: 'number',
                default: null
            },
            maxHeight: {
                type: 'number',
                default: null
            },
            keepProportions: {
                type: 'boolean',
                default: true
            },

            /**
             * Filter
             */
            nextPageToken: {
                type: 'string',
                default: null
            },
            mediaType: {
                type: 'string',
                default: null
            },
            initMediaType: {
                type: 'string',
                default: null
            },
            folders: {
                type: 'string',
                default: null
            },
            tags: {
                type: 'array'
            },
        },

        edit: function(props) {

            var attributes = props.attributes;
            var listTemplate = [];
            var filters = [];
            var nextPageToken = attributes.nextPageToken;
            var templateID = attributes.templateID;
            var contentID = attributes.contentID;
            var embedCode = attributes.embedCode;
            var listFolders = [
                el(
                    'option', {
                        value: ''
                    },
                    i18n.__('All folders', 'thron')
                )
            ];

            function setMediaType(props) {
                THRON.init({
                    clientId: myAjax.thron_clientId,
                    appId: myAjax.thron_appId,
                    appKey: myAjax.thron_appKey
                }).then(function() {
                    THRON.callApi(
                            'GET',
                            '/xcontents/resources/delivery/getContentDetail?clientId=' + myAjax.thron_clientId + '&xcontentId=' + props.attributes.contentID
                        )
                        .then(function(response) {
                            props.setAttributes({
                                mediaType: response.content.contentType
                            });
                        });
                });
            }

            _.each(args.templateList, function(value) {
                listTemplate.push({
                    value: value.id,
                    label: value.name
                })
            });

            props.isSelected && props.attributes.mediaType == null && props.attributes.contentID != null &&
                setMediaType(props);

            filters.push(
                el('select', {
                        className: 'attachment-filters',
                        id: 'media-type',
                        onChange: function(event) {
                            search(event)
                        }
                    },
                    el(
                        'option', {
                            value: ''
                        },
                        i18n.__('All content', 'thron')
                    ),
                    el(
                        'option', {
                            value: 'VIDEO'
                        },
                        i18n.__('Videos', 'thron')
                    ),
                    el(
                        'option', {
                            value: 'AUDIO'
                        },
                        i18n.__('Audio', 'thron')
                    ),
                    el(
                        'option', {
                            value: 'IMAGE'
                        },
                        i18n.__('Images', 'thron')
                    ),
                    el(
                        'option', {
                            value: 'OTHER'
                        },
                        i18n.__('Document (Other)', 'thron')
                    ),
                    el(
                        'option', {
                            value: 'PLAYLIST'
                        },
                        i18n.__('Playlist', 'thron')
                    ),
                    el(
                        'option', {
                            value: 'URL'
                        },
                        i18n.__('URL', 'thron')
                    ),
                    el(
                        'option', {
                        value: 'PAGELET'
                        },
                        i18n.__('Pagelet', 'thron')
                    )
                )
            );

            _.each(args.folders, function(value) {

                listFolders.push(
                    el(
                        'option', {
                            value: value.id
                        },
                        value.name
                    )
                )
            });

            filters.push(
                el('select', {
                        className: 'attachment-filters',
                        id: 'folders',
                        onChange: function(event) {
                            search(event)
                        }

                    },
                    listFolders
                )
            );

            _.each(args.tagsFilter, function(tagList) {

                var options = [
                    el(
                        'option', {
                            value: ''
                        },
                        'All ' + tagList.name
                    )
                ];

                _.each(tagList.list, function(value) {
                    options.push(
                        el(
                            'option', {
                                value: value.id
                            },
                            value.name
                        )
                    )
                });

                filters.push(
                    el('select', {
                            className: 'attachment-filters',
                            id: 'tag-' + tagList.id,
                            onChange: function(event) {
                                search(event);
                            }
                        },
                        options
                    )
                )
            });

            var getEmbedCode = function() {



                if ((templateID != null) && (contentID != null)) {

                    const title = select('core/editor').getEditedPostAttribute('title') != '' ? select('core/editor').getEditedPostAttribute('title') : 'post-id-' + select('core/editor').getEditedPostAttribute('id');

                    /**
                     * Caica la lista dei template
                     *
                     */
                    this.api = THRON.init({
                            clientId: myAjax.thron_clientId,
                            appId: myAjax.thron_appId,
                            appKey: myAjax.thron_appKey
                        })
                        .then(function() {
                            if (embedCode) {
                                if (templateID !== '') {
                                    var tplObj = {
                                        'templateId': templateID,
                                        'templateType': 'CUSTOM'
                                    }
                                } else {
                                    var tplObj = {
                                        'templateId': 'CE1',
                                        'templateType': 'PLATFORM',
                                        'templateVersion': null
                                    }
                                }
                                THRON.callApi(
                                        'POST',
                                        '/xcontents/resources/playerembedcode/update/' + myAjax.thron_clientId, {
                                            body: {
                                                'embedCodeId': embedCode,
                                                'source': {
                                                    'id': contentID,
                                                    'entityType': 'CONTENT'
                                                },
                                                'update': {
                                                    'name': title,
                                                    'template': tplObj,
                                                    'enabled': true
                                                }
                                            }
                                        })
                                    .then(function(response) {

                                        embedCode = response.item.id;

                                        props.setAttributes({
                                            embedCode: embedCode
                                        });
                                    });
                            } else {
                                THRON.callApi(
                                        'POST',
                                        '/xcontents/resources/playerembedcode/insert/' + myAjax.thron_clientId, {
                                            body: {
                                                'skipPkeyCreation': true,
                                                'source': {
                                                    'id': contentID,
                                                    'entityType': 'CONTENT'
                                                },
                                                'value': {
                                                    'name': title,
                                                    'template': {
                                                        'templateId': templateID,
                                                        'templateType': 'CUSTOM'
                                                    },
                                                    'enabled': true
                                                }
                                            }
                                        })
                                    .then(function(response) {

                                        embedCode = response.item.id;
                                        props.setAttributes({
                                            embedCode: embedCode
                                        });
                                    });
                            }

                        });
                }
            };

            var requestId = -1;

            var search = function(event, append) {
                var search = attributes.searchString;
                var folders = attributes.folders;
                var tags = attributes.tags;
                var searchResult = attributes.searchResult;
                var mediaType = attributes.mediaType;

                if (event.target.id == 'media-type') {

                    if (null != event.target.value) {
                        mediaType = event.target.value.trim() == '' ? null : event.target.value.trim();

                        searchResult = new Array();
                        nextPageToken = null;

                        props.setAttributes({
                            //mediaType: mediaType,
                            searchResult: searchResult
                        });
                    }
                }

                if (event.target.id == 'folders') {

                    if (null != event.target.value) {
                        folders = event.target.value.trim() == '' ? null : event.target.value.trim();

                        searchResult = new Array();
                        nextPageToken = null;

                        props.setAttributes({
                            folders: folders,
                            searchResult: searchResult
                        });
                    }
                }

                if (event.target.id.substring(0, 4) == 'tag-') {
                    var new_tags = new Array();
                    searchResult = new Array();
                    nextPageToken = null;

                    var id = event.target.id.substring(4).split(';');

                    _.each(tags, function(tag) {
                        if (id[1] !== tag.parent) {
                            new_tags.push(tag);
                        }
                    });

                    if (null != event.target.value.trim()) {
                        new_tags.push({
                            id: event.target.value.trim(),
                            classificationID: id[0],
                            parent: id[1]
                        });
                    }

                    props.setAttributes({
                        tags: new_tags,
                        searchResult: searchResult
                    });

                    tags = new_tags;

                }

                if (event.target.id === 'media-search-input') {
                    search = event.target.value;
                    searchResult = new Array();
                    nextPageToken = null;

                    props.setAttributes({
                        searchString: search,
                        searchResult: searchResult
                    });

                }

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

                    if (mediaType != null) {
                        body.criteria.contentType = new Array(mediaType);
                    }

                    if (folders != null) {
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

                    if (nextPageToken) {
                        body.pageToken = nextPageToken;
                    }

                    body.responseOptions = new Object();
                    body.responseOptions.resultsPageSize = 50;
                    body.responseOptions.returnDetailsFields = new Array('locales', 'source');

                    var currentRequestId = ++requestId;
                    THRON.callApi(
                        'POST',
                        '/xcontents/resources/content/search/' + myAjax.thron_clientId, { body: body }
                    ).then(function(response) {
                        if (currentRequestId !== requestId) {
                            return;
                        }

                        nextPageToken = response.nextPageToken;

                        var output = append ? searchResult.concat(new Array()) : [];

                        if (response.items.length === 0 && !append) {
                            output.push(
                                el('div', {
                                        className: 'empty-results',
                                    },
                                    i18n.__('No result for this search', 'thron'),
                                )
                            );
                        }

                        _.each(response.items, function(value) {


                            var local_language = value.details.locales[0];

                            _.each(value.details.locales, function(lang) {
                                if (lang.locale == 'EN')
                                    local_language = lang;
                            });
                            _.each(value.details.locales, function(lang) {
                                if (lang.locale == args.wp_language)
                                    local_language = lang;
                            });

                            output.push(
                                el('div', {
                                        className: 'content',
                                        'data-id': value.id,
                                        onClick: function(event) {
                                            var id = null;
                                            var content = null;
                                            _.each(document.getElementsByClassName('content'), function(item) {
                                                if (item.classList.contains('selected'))
                                                    item.classList.remove('selected');
                                            });
                                            content = event.currentTarget;
                                            id = content.getAttribute('data-id');
                                            if (content.classList.contains('selected')) {
                                                content.classList.remove('selected');
                                            } else {
                                                content.classList.add('selected');
                                            }
                                            if (id == null) {
                                                return;
                                            }
                                            props.setAttributes({
                                                tmpContentID: id,
                                                initMediaType: value.contentType
                                            });
                                        }
                                    },
                                    el('picture', {},
                                        el('img', {
                                            className: 'thumb_img',
                                            src: 'https://' + myAjax.thron_clientId + '-view.thron.com/api/xcontents/resources/delivery/getThumbnail/' + myAjax.thron_clientId + '/200x150/' + value.id + '?scalemode=auto'
                                        }),
                                    ),
                                    el('div', {
                                            className: 'label'
                                        },
                                        el('span', {
                                            className: 'thron-type',
                                            'data-value': value.contentType
                                        }),
                                        el('span', {
                                                className: 'thron-extension'
                                            },
                                            typeof value.details.source !== 'undefined' ? value.details.source.extension : value.contentType
                                        ),
                                        el('span', {
                                                className: 'thron-label'
                                            },
                                            local_language.name
                                        ),
                                    )
                                )
                            );
                        });

                        props.setAttributes({
                            searchResult: output,
                            nextPageToken: nextPageToken
                        });

                    });

                })
            };


            if (typeof thePlayer[props.clientId] !== 'undefined') {
                debouncedRenderPlayer(args, props, contentID, embedCode)
            } else {
                if (contentID) wsRenderPlayer(args, props, contentID, embedCode)
            }

            var searchResult = attributes.searchResult;

            var more = [];

            if (nextPageToken != null) {
                more = [
                    el(components.Button, {
                            id: 'thron-more',
                            onClick: function(event) {
                                search(event, true)
                            }
                        },
                        i18n.__('More', 'thron')
                    )
                ]
            }

            var closeModal = function() {
                searchResult = new Array();
                folders = null;
                tags = null;
                searchString = null;
                nextPageToken = null;

                props.setAttributes({
                    isOpen: false,
                    searchResult: new Array(),
                    folders: null,
                    tags: null,
                    searchString: null,
                    nextPageToken: null,
                });
            }

            var dimension = new Array();
            if (props.attributes.embedType != 'responsive') {
                dimension.push(el(components.TextControl, {
                        label: i18n.__('Width (px)', 'thron'),
                        type: 'number',
                        help: '',
                        onChange: function(width) {
                            var aspectRatio;

                            var maxWidth = props.attributes.maxWidth;
                            var maxHeight = props.attributes.maxHeight;

                            aspectRatio = (maxWidth && maxHeight) ? maxHeight / maxWidth : null;

                            props.setAttributes({ width: parseInt(width, 10) });

                            if (aspectRatio && props.attributes.keepProportions) {
                                height = Math.round(width * aspectRatio);
                                props.setAttributes({ height: parseInt(height, 10) });
                            }
                            destroy = true;
                        },
                        value: props.attributes.width
                    }),
                    el(components.TextControl, {
                        label: i18n.__('Height (px)', 'thron'),
                        type: 'number',
                        help: '',
                        onChange: function(height) {

                            var aspectRatio;

                            var maxWidth = props.attributes.maxWidth;
                            var maxHeight = props.attributes.maxHeight;

                            aspectRatio = (maxWidth && maxHeight) ? maxHeight / maxWidth * 100 : null;

                            props.setAttributes({ height: parseInt(height, 10) });

                            if (aspectRatio && props.attributes.keepProportions) {
                                width = Math.round(height / aspectRatio * 100);
                                props.setAttributes({ width: parseInt(width, 10) });
                            }
                            destroy = true;

                        },
                        value: props.attributes.height
                    }),
                    el(components.CheckboxControl, {
                        label: i18n.__('Maintain aspect ratio', 'thron'),
                        onChange: (value) => {
                            props.setAttributes({ keepProportions: value });
                        },
                        checked: props.attributes.keepProportions,
                    }),
                )
            }

            /*thron toolbar / dropdown crop opt*/
            const thronCustomToolbar = el(BlockControls, { key: 'controls', className: 'components-accessible-toolbar block-editor-block-contextual-toolbar' },
                (props.attributes.scalemode != 'manual' || !props.attributes.manualCropSaving) &&
                // Non appare finche non c'e il contenuto
                props.attributes.contentID != null &&
                el('div', { className: 'components-toolbar' },
                    el(MenuItem, {
                        icon: 'edit',
                        onClick: function(event) {
                            props.setAttributes({
                                isOpen: true,
                            });

                            search(event)
                        }
                    })
                ),
                props.attributes.mediaType == 'IMAGE' && props.attributes.contentID != null &&
                // Quando siamo in 'modalità crop' faccio sparire anche la dropdown (come nel blocco immagine standard)
                (props.attributes.scalemode != 'manual' || !props.attributes.manualCropSaving) &&
                el(DropDownMenu, {
                    className: 'components-toolbar components-button components-dropdown-menu__toggle has-icon',
                    icon: initialCropIcon(props),
                    title: 'Crop',
                    'aria-label': 'Crop',
                    label: 'Crop',
                    controls: [{
                            title: 'Auto',
                            icon: props.attributes.scalemode === 'auto' ? AutoNeg : AutoPos,
                            //isDisabled: props.attributes.scalemode === 'auto',
                            onClick: function() {
                                props.setAttributes({
                                    dropDownCropIcon: props.attributes.scalemode !== 'auto' ? AutoPos : CropIcon,
                                    scalemode: props.attributes.scalemode !== 'auto' ? 'auto' : null,
                                    rtieCrop: null,
                                    currentScaleMode: props.attributes.scalemode !== 'auto' ? 'auto' : null
                                });
                                destroy = true;
                            },
                        },
                        {
                            title: 'Centered',
                            icon: props.attributes.scalemode === 'centered' ? CenteredNeg : CenteredPos,
                            //isDisabled: props.attributes.scalemode === 'centered',
                            onClick: function() {
                                props.setAttributes({
                                    dropDownCropIcon: props.attributes.scalemode !== 'centered' ? CenteredPos : CropIcon,
                                    scalemode: props.attributes.scalemode !== 'centered' ? 'centered' : null,
                                    rtieCrop: null,
                                    currentScaleMode: props.attributes.scalemode !== 'centered' ? 'centered' : null
                                });

                                destroy = true;
                            },
                        },
                        {
                            title: 'Product',
                            icon: props.attributes.scalemode === 'product' ? ProductNeg : ProductPos,
                            //isDisabled: props.attributes.scalemode === 'product',
                            onClick: function() {
                                props.setAttributes({
                                    dropDownCropIcon: props.attributes.scalemode !== 'product' ? ProductPos : CropIcon,
                                    scalemode: props.attributes.scalemode !== 'product' ? 'product' : null,
                                    rtieCrop: null,
                                    currentScaleMode: props.attributes.scalemode !== 'product' ? 'product' : null
                                });
                                destroy = true;
                            },
                        },
                        {
                            title: 'Manual',
                            icon: props.attributes.scalemode === 'manual' ? ManualNeg : ManualPos,
                            onClick: function() {
                                props.setAttributes({
                                    manualCropSaving: props.attributes.scalemode !== 'manual' ? true : false,
                                    scalemode: props.attributes.scalemode !== 'manual' ? 'manual' : null,
                                    rtieCrop: null
                                });
                                destroy = true;
                            },
                        }
                    ]
                }),
                props.attributes.mediaType == 'IMAGE' &&
                props.attributes.manualCropSaving && props.attributes.scalemode == 'manual' &&
                el('div', { className: 'components-toolbar-group' },
                    el('input', {
                        type: 'button',
                        value: i18n.__('Apply', 'thron'),
                        className: 'components-button components-toolbar-button thronButton',
                        onClick: function(event) {
                            cropOps(args, props)
                                .then(props)
                                .then(data => {
                                    props.setAttributes({
                                        dropDownCropIcon: ManualPos,
                                        manualCropSaving: false,
                                        rtieCrop: data,
                                        currentScaleMode: 'product'
                                    })
                                })
                            destroy = true;
                        }
                    }),
                    el('input', {
                        type: 'button',
                        value: i18n.__('Cancel', 'thron'),
                        className: 'components-button components-toolbar-button thronButton',
                        onClick: function(event) {
                            props.setAttributes({
                                manualCropSaving: false,
                                scalemode: props.attributes.currentScaleMode
                            });
                            destroy = true;
                        }
                    }),
                )
            );

            return [
                el('div', {
                        className: props.className
                    },

                    /*thron custom toolbar*/
                    thronCustomToolbar,

                    el('div', {
                            className: contentID ? 'block-editor-media-placeholder is-large thron-block' : 'components-placeholder block-editor-media-placeholder is-large thron-block'
                        }, !contentID &&
                        el('div', {
                                className: 'components-placeholder__label'
                            },
                            el('span', {
                                    className: 'block-editor-block-icon block-editor-block-switcher__toggle has-colors'
                                },
                                THRONLogo
                            ),
                            i18n.__('Select a content from THRON', 'thron')
                        ), !contentID &&
                        el('div', {
                                className: 'components-placeholder__instructions'
                            },
                            i18n.__('Choose a content from your THRON Library.', 'thron')
                        ), !contentID &&
                        el(components.Button, {
                                className: 'components-button block-editor-media-placeholder__button block-editor-media-placeholder__upload-button is-primary',
                                id: 'thron-open-modal-button',
                                onClick: function(event) {
                                    props.setAttributes({
                                        isOpen: true,
                                    });

                                    search(event)
                                }
                            },
                            i18n.__('Select a content', 'thron')
                        ), !props.isSelected && contentID &&
                        el('div', {
                            className: 'thron-block-overlay'
                        }, ),
                        el('div', {
                                id: 'thron-preview-id-' + props.clientId + '-wrapper',
                            },
                            el('div', {
                                id: 'thron-preview-id-' + props.clientId,
                                className: 'thron-block-preview'
                            })
                        ),
                        el('style', {},
                            (props.attributes.embedType == 'responsive') ?
                            '#thron-preview-id-' + props.clientId + '{ width: 100%; height:100%; position: absolute; top: 0} ' +
                            '#thron-preview-id-' + props.clientId + '-wrapper {padding-top:' + (attributes.maxHeight / attributes.maxWidth * 100) + '%; width: 100%; position: relative}' :
                            '#thron-preview-id-' + props.clientId + '{ width: ' + attributes.width + 'px; height: ' + attributes.height + 'px; } '
                        ),

                        //!props.isSelected && props.attributes.scalemode == 'manual' &&
                        props.isSelected && props.attributes.scalemode == 'manual' &&
                        el('div', {
                            className: 'components-placeholder__label'
                        }, ),
                        // Modal
                        attributes.isOpen &&
                        el(components.Modal, {
                                title: i18n.__('Select a content from THRON', 'thron'),
                                className: 'thron-block-modal',
                                onRequestClose: closeModal
                            },
                            //Media Content
                            el('div', {
                                    className: 'thron-block',
                                    id: 'thron_content',
                                },
                                //Header modal
                                el('div', {
                                        className: 'media-toolbar wsthron-header',
                                        id: 'thron_toolbar',
                                    },
                                    el('div', {
                                            className: 'media-toolbar-secondary',
                                        },
                                        filters,
                                    ),
                                    el('div', {
                                            className: 'media-toolbar-primary search-form',
                                        },
                                        el('label', {
                                                className: 'media-search-input-label',
                                                htmlFor: 'media-search-input'
                                            },
                                            i18n.__('Search', 'thron')
                                        ),
                                        el('input', {
                                            className: 'search',
                                            id: 'media-search-input',
                                            onChange: search
                                        }),
                                    ),
                                ),
                                //End Header modal
                                // Search result
                                el('div', {
                                        id: 'thron_list',
                                        class: 'td-content-grid',
                                        onScroll: function(event) {
                                            var scrollTop = document.getElementById('thron_list').scrollTop
                                            var scrollHeight = document.getElementById('thron_list').scrollHeight
                                            var clientHeight = document.getElementById('thron_list').clientHeight
                                            if (scrollTop + clientHeight === scrollHeight) {
                                                if (typeof attributes.nextPageToken !== null && typeof attributes.nextPageToken !== 'undefined') {
                                                    search(event, true);
                                                }
                                            }
                                        }
                                    },
                                    searchResult
                                ),
                                el('div', {
                                        class: 'modal-footer media-toolbar-primary search-form'
                                    },
                                    el(components.Button, {
                                            className: 'button button-large media-button media-button-insert',
                                            onClick: function() {

                                                /**
                                                 * if the content is changed it starts the sequence to change
                                                 * the embed code
                                                 */
                                                if (attributes.tmpContentID != contentID) {

                                                    embedCode = null;

                                                    props.setAttributes({ embedCode: embedCode });

                                                    contentID = attributes.tmpContentID;

                                                    /**
                                                     * Request content detils
                                                     */
                                                    THRON.callApi(
                                                            'GET',
                                                            '/xcontents/resources/delivery/getContentDetail?clientId=' + myAjax.thron_clientId + '&xcontentId=' + contentID
                                                        )
                                                        .then(function(response) {

                                                            deliverySize = response.content.deliverySize.aspectRatio.split(':');

                                                            props.setAttributes({
                                                                maxWidth: parseInt(deliverySize[0], 10),
                                                                maxHeight: parseInt(deliverySize[1], 10),
                                                            });
                                                        });

                                                    props.setAttributes({
                                                        contentID: contentID,
                                                    });

                                                    getEmbedCode();
                                                }

                                                // Close Modal Frame
                                                closeModal();

                                                props.setAttributes({
                                                    mediaType: props.attributes.initMediaType,
                                                    //searchResult: searchResult
                                                });

                                            }
                                        },
                                        i18n.__('Insert content', 'thron')
                                    ),
                                ),
                            ),
                        ),
                        // End Modal



                        // SideBar
                        el(InspectorControls, { key: 'inspector' }, // Display the block options in the inspector panel.
                            el(PanelBody, {
                                    title: i18n.__('THRON OPTION', 'thron'),
                                    className: '',
                                    initialOpen: true
                                },
                                el(components.SelectControl, {
                                    label: i18n.__('Select template', 'thron'),
                                    options: listTemplate,
                                    onChange: (value) => {
                                        templateID = value;

                                        props.setAttributes({ templateID: templateID });

                                        getEmbedCode();

                                        destroy = true;
                                    },
                                    value: props.attributes.templateID
                                }),
                                el(components.SelectControl, {
                                    label: i18n.__('Select embed type', 'thron'),
                                    options: [{
                                            label: i18n.__('Responsive', 'thron'),
                                            value: 'responsive',
                                        },
                                        {
                                            label: i18n.__('Fixed', 'thron'),
                                            value: 'fixed'
                                        },
                                    ],
                                    onChange: (value) => {
                                        props.setAttributes({ embedType: value });
                                    },
                                    value: props.attributes.embedType
                                }),
                                dimension
                            ),
                            /* props.attributes.mediaType == 'IMAGE' &&
                            el(PanelBody, {
                                    title: i18n.__('RTIE Params', 'thron'),
                                    className: '',
                                    initialOpen: true
                                },
                            ), */
                            props.attributes.mediaType == 'IMAGE' &&
                            el(PanelBody, {
                                    title: i18n.__('Image adjustments', 'thron'),
                                    className: '',
                                    initialOpen: true
                                },
                                el(components.RangeControl, {
                                    label: i18n.__('Quality', 'thron'),
                                    min: 0,
                                    default: 90,
                                    max: 100,
                                    step: 5,
                                    type: 'number',
                                    onChange: function(quality) {
                                        props.setAttributes({ 'quality': quality })
                                        destroy = true;
                                    },
                                    value: props.attributes.quality
                                }),
                                el(components.RangeControl, {
                                    label: i18n.__('Brightness', 'thron'),
                                    min: -100,
                                    max: 100,
                                    default: 0,
                                    step: 5,
                                    help: '',
                                    onChange: function(brightness) {
                                        props.setAttributes({ 'brightness': brightness })
                                        destroy = true;
                                    },
                                    value: props.attributes.brightness
                                }),
                                el(components.RangeControl, {
                                    label: i18n.__('Contrast', 'thron'),
                                    min: -100,
                                    max: 100,
                                    default: 0,
                                    step: 5,
                                    help: '',
                                    onChange: function(contrast) {
                                        props.setAttributes({ 'contrast': contrast })
                                        destroy = true;
                                    },
                                    value: props.attributes.contrast
                                }),
                                el(components.RangeControl, {
                                    label: i18n.__('Sharpness', 'thron'),
                                    min: -100,
                                    max: 100,
                                    default: 0,
                                    step: 5,
                                    help: '',
                                    onChange: function(sharpness) {
                                        props.setAttributes({ 'sharpness': sharpness })
                                        destroy = true;
                                    },
                                    value: props.attributes.sharpness
                                }),
                                el(components.RangeControl, {
                                    label: i18n.__('Color', 'thron'),
                                    min: -100,
                                    max: 100,
                                    default: 0,
                                    step: 5,
                                    help: '',
                                    onChange: function(color) {
                                        props.setAttributes({ 'color': color })
                                        destroy = true;
                                    },
                                    value: props.attributes.color
                                }),
                            ),
                        ),
                        // End SideBar
                    ),
                )
            ]
        },

        save: function(props) {

            var aspectRatio = 75;

            var maxWidth = props.attributes.maxWidth;
            var maxHeight = props.attributes.maxHeight;

            aspectRatio = (maxWidth && maxHeight) ? maxHeight / maxWidth * 100 : 75;

            return el('div', {
                    className: props.className,
                },
                '[thron contentID="' + props.attributes.contentID + '" embedCodeId="' + props.attributes.embedCode +
                '" embedType="' + props.attributes.embedType + '" width="' + props.attributes.width +
                '" quality="' + props.attributes.quality +
                '" scalemode="' + props.attributes.scalemode +
                (props.attributes.scalemode == 'manual' &&
                    typeof props.attributes.rtieCrop != 'undefined' &&
                    props.attributes.rtieCrop != null ? '" cropx="' + props.attributes.rtieCrop.cropx +
                    '" cropy="' + props.attributes.rtieCrop.cropy +
                    '" croph="' + props.attributes.rtieCrop.croph +
                    '" cropw="' + props.attributes.rtieCrop.cropw : '') +
                '" brightness="' + (props.attributes.brightness + 100) + '" contrast="' + (props.attributes.contrast + 100) +
                '" sharpness="' + (props.attributes.sharpness + 100) + '" color="' + (props.attributes.color + 100) +
                '" height="' + props.attributes.height + '" aspectRatio="' + aspectRatio + '" ]',
            );


        },


        deprecated: [{
            attributes: {
                manualCropSaving: {
                    type: 'boolean',
                    default: false
                },
                isOpen: {
                    type: 'boolean',
                    default: false
                },
                searchString: {
                    type: 'string',
                    default: ''
                },
                searchResult: {
                    type: 'array',
                    default: []
                },
                tmpContentID: {
                    type: 'string',
                    default: null
                },
                contentID: {
                    type: 'string',
                    default: null
                },
                templateID: {
                    type: 'string',
                    default: args.thron_playerTemplates
                },
                embedCode: {
                    type: 'string',
                    default: null
                },
                scalemode: {
                    type: 'string',
                    default: 'auto'
                },
                currentScaleMode: {
                    type: 'string',
                    default: null
                },
                rtieCrop: {
                    cropx: {
                        type: 'number',
                        default: 0
                    },
                    cropy: {
                        type: 'number',
                        default: 0
                    },
                    cropw: {
                        type: 'number',
                        default: 0
                    },
                    croph: {
                        type: 'number',
                        default: 0
                    }
                },
                quality: {
                    type: 'number',
                    default: 90
                },
                brightness: {
                    type: 'number',
                    default: 0
                },
                contrast: {
                    type: 'number',
                    default: 0
                },
                sharpness: {
                    type: 'number',
                    default: 0
                },
                color: {
                    type: 'number',
                    default: 0
                },

                /**
                 * Dimension
                 */
                embedType: {
                    type: 'string',
                    default: 'responsive'
                },
                width: {
                    type: 'number',
                    default: 100
                },
                height: {
                    type: 'number',
                    default: null
                },
                maxWidth: {
                    type: 'number',
                    default: null
                },
                maxHeight: {
                    type: 'number',
                    default: null
                },
                keepProportions: {
                    type: 'boolean',
                    default: true
                },

                /**
                 * Filter
                 */
                nextPageToken: {
                    type: 'string',
                    default: null
                },
                mediaType: {
                    type: 'string',
                    default: null
                },
                folders: {
                    type: 'string',
                    default: null
                },
                tags: {
                    type: 'array'
                },
            },

            save(props) {

                var aspectRatio = 75;

                var maxWidth = props.attributes.maxWidth;
                var maxHeight = props.attributes.maxHeight;

                aspectRatio = (maxWidth && maxHeight) ? maxHeight / maxWidth * 100 : 75;
               
                return el('div', {
                        className: props.className,
                        
                    },
                    '[thron contentID="' + props.attributes.contentID + '" embedCodeId="' + props.attributes.embedCode + '" embedType="' + props.attributes.embedType + '" width="' + props.attributes.width + '" height="' + props.attributes.height + '" aspectRatio="' + aspectRatio + '"]',
                );
            },
        }]



    })
})
(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window._,
)