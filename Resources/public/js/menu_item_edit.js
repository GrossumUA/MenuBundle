$(function()
{
    "use strict";

    var $head             = $(document.head),
        $entityClass      = $('#' + $head.find('meta[name="grossum_menu.form.entity_class.id"]').attr('content')),
        $entityIdentifier = $('#' + $head.find('meta[name="grossum_menu.form.entity_identifier.id"]').attr('content')),
        $form             = $entityClass.closest('form'),
        url               = $head.find('meta[name="grossum_menu.route.edit"]').attr('content');

    $entityClass.change(function() {
        $.ajax({
            url :  url,
            type:  'POST',
            data : $form.serialize(),
            beforeSend: function()
            {
                $entityClass.prop('disabled', true);
                $entityIdentifier.prop('disabled', true);
            },
            success: function(response)
            {
                if (response.result) {
                    var $newEntityIdentifiers = $(response.entityIdentifiers);

                    $entityIdentifier.empty().append($newEntityIdentifiers.children()).change();
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
