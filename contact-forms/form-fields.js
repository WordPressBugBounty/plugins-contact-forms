var accuaWidgets = {};
(function($) {

accuaWidgets = {

    init : function() {
        var rem, sidebars = $('div.widgets-sortables'), isRTL = !! ( 'undefined' != typeof isRtl && isRtl ),
            margin = ( isRtl ? 'marginRight' : 'marginLeft' ), the_id;
        
        //Evento per widget-action (aggiunto in modalità "live")
        // $('a.widget-action').live('click', function(){
        $(document).on('click', 'a.widget-action', function(){
            var css = {}, widget = $(this).closest('div.widget'), inside = widget.children('.widget-inside'), w = parseInt( widget.find('input.widget-width').val(), 10 );

            if ( inside.is(':hidden') ) {
                if ( w > 250 && inside.closest('div.widgets-sortables').length ) {
                    css['width'] = w + 30 + 'px';
                    if ( inside.closest('div.widget-liquid-right').length )
                        css[margin] = 235 - w + 'px';
                    widget.css(css);
                }
                accuaWidgets.fixLabels(widget);
                inside.slideDown('fast');
            } else {
                inside.slideUp('fast', function() {
                    widget.css({'width':'', margin:''});
                });
            }
            return false;
        });

        //Click sul bottone di salvataggio
        // $('input.widget-control-save').live('click', function(){
        $(document).on('click', 'input.widget-control-save', function(){
            accuaWidgets.save( $(this).closest('div.widget'), 0, 1, 0 );
            return false;
        });

        //Click sul bottone di rimozione
        //$('a.widget-control-remove').live('click', function(){
        $(document).on('click', 'a.widget-control-remove', function(){
            accuaWidgets.save( $(this).closest('div.widget'), 1, 1, 0 );
            return false;
        });

        //Click sul bottone di chiusura
        //$('a.widget-control-close').live('click', function(){
        $(document).on('click', 'a.widget-control-close', function(){
            accuaWidgets.close( $(this).closest('div.widget') );
            return false;
        });

        //Impostazione titolo e apertura widget se c'è un errore
        sidebars.children('.widget').each(function() {
            accuaWidgets.appendTitle(this);
            if ( $('p.widget-error', this).length )
                $('a.widget-action', this).click();
        });

        //Trascinabilità dei widget nella lista
        $('#widget-list').children('.widget').draggable({
            connectToSortable: 'div.widgets-sortables',
            handle: '> .widget-top > .widget-title',
            distance: 2,
            helper: 'clone',
            zIndex: 5,
            containment: 'document',
            start: function(e,ui) {
                accuaWidgets.fixWebkit(1);
                ui.helper.find('div.widget-description').hide();
                the_id = this.id;
            },
            stop: function(e,ui) {
                if ( rem )
                    $(rem).hide();
                rem = '';
                accuaWidgets.fixWebkit();
            }
        });

        //Ordinabilità dei widget
        sidebars.sortable({
            placeholder: 'widget-placeholder',
            items: '> .widget',
            handle: '> .widget-top > .widget-title',
            cursor: 'move',
            distance: 2,
            containment: 'document',
            start: function(e,ui) {
                accuaWidgets.fixWebkit(1);
                ui.item.children('.widget-inside').hide();
                ui.item.css({margin:'', 'width':''});
            },
            stop: function(e,ui) {
                if ( ui.item.hasClass('ui-draggable') && ui.item.data('draggable') )
                    ui.item.draggable('destroy');

                if ( ui.item.hasClass('deleting') ) {
                    accuaWidgets.save( ui.item, 1, 0, 1 ); // delete widget
                    ui.item.remove();
                    return;
                }

                var add = ui.item.find('input.add_new').val(),
                    n = ui.item.find('input.multi_number').val(),
                    id = the_id,
                    sb = $(this).attr('id');

                ui.item.css({margin:'', 'width':''});
                the_id = '';
                
                accuaWidgets.fixWebkit();
                if ( add ) {
                    if ( 'multi' == add ) {
                        ui.item.html( ui.item.html().replace(/<[^<>]+>/g, function(m){ return m.replace(/__i__|%i%/g, n); }) );
                        ui.item.attr( 'id', id.replace(/__i__|%i%/g, n) );
                        n++;
                        $('div#' + id).find('input.multi_number').val(n);
                    } else if ( 'single' == add ) {
                        ui.item.attr( 'id', 'new-' + id );
                        rem = 'div#' + id;
                    }
                    accuaWidgets.save( ui.item, 0, 0, 1 );
                    ui.item.find('input.add_new').val('');
                    ui.item.find('a.widget-action').click();
                    return;
                }
                accuaWidgets.saveOrder(sb);
            },
            receive: function(e,ui) {
                var t = $(this);
                if ( !t.is(':visible') ) {
                    t.sortable('cancel');
                }
            }
        }).sortable('option', 'connectWith', 'div.widgets-sortables').parent().filter('.closed').children('.widgets-sortables').sortable('disable');

        //Droppabilità dei widget disponibili
        $('#available-widgets').droppable({
            tolerance: 'pointer',
            accept: function(o){
                return $(o).parent().attr('id') != 'widget-list';
            },
            drop: function(e,ui) {
                ui.draggable.addClass('deleting');
                $('#removing-widget').hide().children('span').html('');
            },
            over: function(e,ui) {
                ui.draggable.addClass('deleting');
                $('div.widget-placeholder').hide();

                if ( ui.draggable.hasClass('ui-sortable-helper') )
                    $('#removing-widget').show().children('span')
                    .html( ui.draggable.find('div.widget-title').children('h4').html() );
            },
            out: function(e,ui) {
                ui.draggable.removeClass('deleting');
                $('div.widget-placeholder').show();
                $('#removing-widget').hide().children('span').html('');
            }
        });
        
        var holders = $('#widgets-left .widget-holder, #widgets-right .widgets-sortables');
        holders.wrap('<div class="accua-form-widget-scroll-wrapper"></div>');
       /*
        $('.accua-form-widget-scroll-wrapper').each(function(){
          var t = $(this);
          //t.css({'height': t.height()});
          t.resizable({handles: 's'});
        });*/
    },

    //Salvataggio ordine
    saveOrder : function(sb) {
        if ( sb )
            $('#' + sb).closest('div.widgets-holder-wrap').find('img.ajax-feedback').css('visibility', 'visible');

        var a = {
            action: 'accua-form-fields-order',
            _nonce_edit_form: $('#_nonce_edit_form').val(),
            sidebars: []
        };

        $('div.widgets-sortables').each( function() {
          var t = $(this);
          if ( t.sortable ) {
            a['sidebars[' + t.attr('id') + ']'] = t.sortable('toArray').join(',');
          }
        });

        $.post( ajaxurl, a, function() {
            $('img.ajax-feedback').css('visibility', 'hidden');
        });

        this.resize();
    },

    //Salvataggio stato widget
    save : function(widget, del, animate, order) {
        var sb = widget.closest('div.widgets-sortables').attr('id'), data = widget.find('form').serialize(), a;
        widget = $(widget);
        $('.ajax-feedback', widget).css('visibility', 'visible');

        a = {
            action: 'accua-save-form-field',
            _nonce_edit_form: $('#_nonce_edit_form').val(),
            sidebar: sb
        };

        if ( del )
            a['delete_widget'] = 1;

        data += '&' + $.param(a);

        $.post( ajaxurl, data, function(r){
            var id;

            if ( del ) {
                if ( !$('input.widget_number', widget).val() ) {
                    id = $('input.widget-id', widget).val();
                    $('#available-widgets').find('input.widget-id').each(function(){
                        if ( $(this).val() == id )
                            $(this).closest('div.widget').show();
                    });
                }

                if ( animate ) {
                    order = 0;
                    widget.slideUp('fast', function(){
                        $(this).remove();
                        accuaWidgets.saveOrder();
                    });
                } else {
                    widget.remove();
                    accuaWidgets.resize();
                }
            } else {
                $('.ajax-feedback').css('visibility', 'hidden');
                if ( r && r.length > 2 ) {
                    $('div.widget-content', widget).html(r);
                    accuaWidgets.appendTitle(widget);
                    accuaWidgets.fixLabels(widget);
                }
            }
            if ( order )
                accuaWidgets.saveOrder();
        });
    },

    //Aggiunta titolo
    appendTitle : function(widget) {
        var title = $('input[id*="-title"]', widget);
        if ( title = title.val() ) {
            title = title.replace(/<[^<>]+>/g, '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            $(widget).children('.widget-top').children('.widget-title').children()
                .children('.in-widget-title').html(': ' + title);
        }
    },

    resize : function() {
      /*
        $('div.widgets-sortables').not('#wp_inactive_widgets').each(function(){
            var t = $(this);
            var h = 50, H = t.children('.widget').length;
            h = h + parseInt(H * 48, 10);
            t.css( 'minHeight', h + 'px' );
        });
      */
    },

    fixWebkit : function(n) {
        n = n ? 'none' : '';
        $('body').css({
            WebkitUserSelect: n,
            KhtmlUserSelect: n
        });
    },

    fixLabels : function(widget) {
        widget.children('.widget-inside').find('label').each(function(){
            var t = $(this);
            var f = t.attr('for');
            if ( f && f == $('input', this).attr('id') ) {
                t.removeAttr('for');
            }
        });
    },

    //Chiusura widget
    close : function(widget) {
        widget.children('.widget-inside').slideUp('fast', function(){
            widget.css({'width':'', margin:''});
        });
    }
};

$(document).ready(function($){ accuaWidgets.init(); });

})(jQuery);
