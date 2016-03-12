$(function()
{
    "use strict";

    var $head             = $(document.head),
        $entityClass      = $('#' + $head.find('meta[name="grossum_menu.form.entity_class.id"]').attr('content')),
        $entityIdentifier = $('#' + $head.find('meta[name="grossum_menu.form.entity_identifier.id"]').attr('content')),
        placeHolderHtml   = null,
        url               = $head.find('meta[name="grossum_menu.route.edit"]').attr('content');

    $entityClass.change(function() {
        $entityClass.prop('disabled', true);
        $entityIdentifier.prop('disabled', true);

        $.ajax({
            'url':  url,
            'type': 'POST',
            'data': {
                'entityClass': $entityClass.val()
            },
            success: function(response)
            {
                if (response.result) {
                    var entityIdentifiers = response.entityIdentifiers;

                    if (placeHolderHtml === null) {
                        var $tempDiv = $('<div>');

                        $tempDiv.empty();
                        $tempDiv.append($entityIdentifier.find('option:first').clone());

                        placeHolderHtml = $tempDiv.html();
                    }

                    var newOptions = placeHolderHtml;

                    for (var id in entityIdentifiers) {
                        if (!entityIdentifiers.hasOwnProperty(id)) {
                            continue;
                        }

                        newOptions += '<option value="' + id + '">' + entityIdentifiers[id] + '</option>';
                    }

                    $entityIdentifier.html(newOptions).change();
                }
            },
            complete: function()
            {
                $entityClass.prop('disabled', false);
                $entityIdentifier.prop('disabled', false);
            }
        });
    });
});
