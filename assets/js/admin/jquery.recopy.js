/**
 * jQuery-Plugin "relCopy"
 *
 * @version: 1.1.0, 25.02.2010
 *
 * @author: Andres Vidal
 *          code@andresvidal.com
 *          http://www.andresvidal.com
 *
 * Instructions: Call $(selector).relCopy(options) on an element with a jQuery type selector
 * defined in the attribute "rel" tag. This defines the DOM element to copy.
 * @example: $('a.copy').relCopy({limit: 5}); // <a href="example.com" class="copy" rel=".phone">Copy Phone</a>
 *
 * @param: string    excludeSelector - A jQuery selector used to exclude an element and its children
 * @param: integer    limit - The number of allowed copies. Default: 0 is unlimited
 * @param: string    append - HTML to attach at the end of each copy. Default: remove link
 * @param: string    copyClass - A class to attach to each copy
 * @param: boolean    clearInputs - Option to clear each copies text input fields or textarea
 *
 */

(function ($) {

    $.fn.relCopy = function (options) {
        var settings = jQuery.extend({
            excludeSelector: ".exclude",
            emptySelector: ".empty",
            copyClass: "copy",
            append: '',
            clearInputs: true,
            limit: 0 // 0 = unlimited
        }, options);

        settings.limit = parseInt(settings.limit);

        // loop each element
        this.each(function () {

            // set click action
            $(this).click(function () {
                var rel = $(this).attr('rel'); // rel in jquery selector format
                var counter = $(rel).length;

                // stop limit
                if (settings.limit != 0 && counter >= settings.limit) {
                    return false;
                }
                ;

                var master = $(rel + ":first");
                var parent = $(master).parent();
                var clone = $(master).clone(true).addClass(settings.copyClass + counter).append(settings.append);

                //Remove Elements with excludeSelector
                if (settings.excludeSelector) {
                    $(clone).find(settings.excludeSelector).remove();
                }
                ;

                //Empty Elements with emptySelector
                if (settings.emptySelector) {
                    $(clone).find(settings.emptySelector).empty();
                }
                ;

                // Increment Clone IDs
                if ($(clone).attr('id')) {
                    var newid = $(clone).attr('id') + (counter + 1);
                    $(clone).attr('id', newid);
                }
                ;

                // Increment Clone Children IDs
                $(clone).find('[id]').each(function () {
                    //var newid = $(this).attr('id') + (counter + 1);
                    //$(this).attr('id', newid);

                    console.log($(this).attr('id'));

                    $(this).attr("id", function (i, oldVal) {
                        if (!oldVal) {
                            return false;
                        }
                        return oldVal.replace(/_(\d+)/, function (_, m) {
                            return "_" + (counter + 1);
                        });
                    });
                });

                //Clear Inputs/Textarea
                if (settings.clearInputs) {
                    $(clone).find(':input').each(function () {
                        var type = $(this).attr('type');
                        switch (type) {
                            case "button":
                                $(this).attr("onclick", function (i, oldVal) {
                                    if (!oldVal) {
                                        return false;
                                    }
                                    return oldVal.replace(/_(\d+)/, function (_, m) {
                                        return "_" + ($(rel).length + 1);
                                    });
                                });
                                break;
                            case "reset":
                                break;
                            case "submit":
                                break;
                            case "checkbox":
                                $(this).attr('checked', '');
                                break;
                            default:
                                $(this).val("");
                        }

                        $(this).attr("name", function (i, oldVal) {
                            if (!oldVal) {
                                return false;
                            }
                            return oldVal.replace(/\[(\d+)\]/, function (_, m) {
                                return "[" + $(rel).length + "]";
                            });
                        });
                    });

                    /*
                     $(clone).find("input").attr("name",function(i,oldVal) {
                     return oldVal.replace(/\[(\d+)\]/,function(_,m){
                     return "[" + $(rel).length + "]";
                     });
                     });*/

                    // Clear image values.
                    $(clone).find('.bookdb-uploaded-image').each(function () {
                        $(this).attr('src', '').hide();
                    });

                    // Replace the Title
                    $(clone).find('h4').each(function () {
                        $(this).text(function (text) {
                            return $(this).text().replace('1', function (_, m) {
                                return $(rel).length + 1;
                            });
                        });
                    });
                }
                ;

                // Fix dem buttons.
                $(clone).find('button').each(function () {
                    $(this).attr('onclick', 'jQuery(this).parent().parent().remove(); return false');
                });

                $(parent).find(rel + ':last').after(clone);

                return false;

            }); // end click action

        }); //end each loop

        return this; // return to jQuery
    };

})(jQuery);