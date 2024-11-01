"use strict";

/**
 * WordPress dependencies.
 */
var __ = wp.i18n.__;
var PanelBody = wp.components.PanelBody;
var Button = wp.components.Button;
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var _wp$blockEditor = wp.blockEditor,
AlignmentToolbar = _wp$blockEditor.AlignmentToolbar,
BlockControls = _wp$blockEditor.BlockControls;
var Fragment = wp.element.Fragment;
var addFilter = wp.hooks.addFilter;

var markBlockAsThron = createHigherOrderComponent(function(BlockEdit){
    return function (props){

        if('core/image' === props.name) {
            return React.createElement(Fragment, null,
                React.createElement(BlockEdit, props), 

                typeof props.attributes.url !== 'undefined' && 
                props.attributes.url.indexOf('cdn.thron.com') !== -1 && 

                    React.createElement(BlockControls, null,
                        React.createElement('span', {
                            className: 'hasThronImage'
                        })
                    )
            )
        }
        return React.createElement(BlockEdit, props);
    };
}, 'markBlockAsThron');
 
 addFilter( 'editor.BlockEdit', 'thron-imageblock-toolbar-override', markBlockAsThron );