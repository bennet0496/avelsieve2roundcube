AVELSIEVE.edit = {

    checkOther: function (id){
        for(var i=0;i<document.addrule.length;i++){
            if(document.addrule.elements[i].value == id){
                document.addrule.elements[i].checked = true;
            }
        }
    },

    toggleShowDiv: function(divname) {
        if(el(divname)) {
            if(el(divname).style.display == "none") {
                el(divname).style.display = "";
            } else {
                el(divname).style.display = "none";
            }
        }	
    },

    toggleShowDivWithImg: function(divname,scriptaculous) {
        if(el(divname)) {
            img_name = divname + '_img';
            if(el(divname).style.display == "none") {
                if(scriptaculous == 1) {
                    Effect.toggle(divname, 'slide');
                } else {
                    el(divname).style.display = "";
                }
                if(document[img_name]) {
                    document[img_name].src = "images/opentriangle.gif";
                }	
                if(el('divstate_' + divname )) {
                    el('divstate_'+divname).value = 1;
                }
            } else {
                if(scriptaculous == 1) {
                    Effect.toggle(divname, 'slide');
                } else {
                    el(divname).style.display = "none";
                }
                if(document[img_name]) {
                    document[img_name].src = "images/triangle.gif";
                }	
                if(el('divstate_'+divname)) {
                    el('divstate_'+divname).value = 0;
                }
            }
        }
    },

    alsoCheck: function(me,group) {
        var checked = me.checked; 
        if (checked) for (var i = 1; i < arguments.length; i++) { 
            var ck = document.getElementById(arguments[i]); 
            if (ck) ck.checked = true; 
        }
    },

    alsoUnCheck: function(me,group) {
        var checked = me.checked; 
        if (checked == false) for (var i = 1; i < arguments.length; i++) { 
            var ck = document.getElementById(arguments[i]); 
            if (ck) ck.checked = false; 
        }
    },

    radioCheck: function(me,group) {
        var checked = me.checked; 
        if (checked) for (var i = 1; i < arguments.length; i++) { 
            var ck = document.getElementById(arguments[i]); 
            if (ck) ck.checked = false; 
        } else {
            return;
        }
        me.checked = checked; // checkbox action 
        //me.checked = true; // radiobox action 
    },

    /**
     *
     * index is the condition index (0, 1, ...) or -1 to add a new one.
     */
    changeCondition: function(index, newtype) {
        if(index == -1) {
            var index = Number($('condition_items').value); 
            $('avelsieveconditionless').disabled = false;
        }

        new Ajax.Request('ajax_handler.php', {
            method:'get',
            parameters: {avaction: 'edit_condition', type: newtype, index: index},
            onSuccess: function(transport){
              var response = transport.responseText || "no response text";
              if( $('condition_line_' + index) ) {
                  $('condition_line_' + index).innerHTML = response;
              } else {
                // index does not exist
                var lastindex = $('condition_items').value - 1;
                var newindex = lastindex + 1;

                $('conditions').insert(response);
                $('condition_items').value = Number($('condition_items').value) + 1;
              }

            },
            onFailure: function(){ alert('Something went wrong...') }
        }
        );
    },

    deleteLastCondition: function() {
        var lastindex = $('condition_items').value - 1;
        if($('condition_line_' + lastindex)) {
            if(lastindex > 0) {
                $('condition_line_' + lastindex).remove();
                $('condition_items').value = Number($('condition_items').value) - 1;
            } else if (lastindex == 0) {
                $('avelsieveconditionless').disabled = true;
            }
        }
    },

    changeConditionKind: function(index, value) {
        new Ajax.Request('ajax_handler.php', {
            method:'get',
            parameters: {avaction: 'edit_condition_kind', value: value, index: index},
            onSuccess: function(transport){
              var response = transport.responseText || "no response text";
              if( $('condition_line_' + index) ) {
                  $('condition_line_' + index).innerHTML = response;
              } else {
                // index does not exist
              }
            },
            onFailure: function(){ alert('Something went wrong...') }
        }
        );
    },

    datetimeGetChildren: function(name, index) {
        
        // Temporarily make input disabled.
        $('datetime_input_'+name+'_'+index).disabled = true;

        var value = $('datetime_input_'+name+'_'+index).value;
        if(value == '') {
            $('datetime_condition_after_' + name+'_'+index).innerHTML = '';
        } else {
            var params = $('avelsieve_addrule').serialize(true);
            params.avaction = 'datetime_get_snippet';
            params.varname = name;
            params.varvalue = value;
            params.index = index;

            new Ajax.Request('ajax_handler.php', {
                method:'post',
                parameters: params,
                onSuccess: function(transport){
                  var response = transport.responseText.evalJSON() || "no response text";
                  $('datetime_condition_after_' + name+'_'+index).innerHTML = response.html;

                  // For future performance fix.
                  // if(response.triggerDatepickerCreation != null)
                  AVELSIEVE.edit.setupDatepickers();

                  // Remove disabled status
                  $('datetime_input_'+name+'_'+index).disabled = false;
                },
                onFailure: function(){ alert('Something went wrong...') }
            });
        }
    },

    /**
     * Set up datepicker controls for all elements with class 'avelsieve_datepicker'
     *
     * Note: The datepicker icon would be nice, but it doesn't allow proper inline positioning
     *  of the datepicker input element. e.g.:
     *  // var picker = new Control.DatePicker(s, {icon: 'images/calendar.png', datePicker: true});  
     */
    setupDatepickers: function() {
        // date
        $$('#conditions .avelsieve_datepicker_date').each(function(s) {
            new Control.DatePicker(s, {datePicker: true, timePicker: false, locale: 'en_iso8601'});
            s.removeClassName('avelsieve_datepicker_date');
        });
        // time
        $$('#conditions .avelsieve_datepicker_time').each(function(s) {
            new Control.DatePicker(s, {datePicker: false, timePicker: true, locale: 'en_iso8601'});  
            s.removeClassName('avelsieve_datepicker_time');
        });
    }
}

Event.observe(window, 'load', function() {
    AVELSIEVE.edit.setupDatepickers();
});

