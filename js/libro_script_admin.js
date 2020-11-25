jQuery(document).ready(function () {

    jQuery("#dep").select();
    jQuery("#prov").select();

    function admin_departamento(select) {
        var data = {
            'action': 'rt_libro_load_provincias_front',
            'idDep': jQuery(select).val()
        }

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function (response) {
                jQuery('#prov').html('<option>Seleccione un provincia</option>');
                jQuery('#dist').html('<option>Seleccione un distrito</option>');

                if (response) {
                    for (var r in response) {
                        jQuery('#prov').append('<option value=' + r + '>' + response[r] + '</option>');
                    }
                }
            }
        })
    }

    function admin_provincia(select) {
        var data = {
            'action': 'rt_libro_load_distrito_front',
            'idProv': jQuery(select).val()
        }

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function (response) {
                jQuery('#dist').html('<option>Seleccione un distrito</option>');

                if (response) {
                    for (var r in response) {
                        jQuery('#dist').append('<option value=' + r + '>' + response[r] + '</option>');
                    }
                }
            }
        })
    }

    jQuery('#dep').on('change', function () {
        admin_departamento(this);
    });

    jQuery('#prov').on('change', function () {
        admin_provincia(this);
    });

    jQuery(".edad").click(function (evento) {

        var edad = jQuery('input:radio[name=flag_menor]:checked').val();

        if (edad == '1') {
            jQuery("#title_tutor").css("display", "block");
            jQuery("#datos_tutor").css("display", "block");
            jQuery("#doc_tutor").css("display", "block");
        } else {
            jQuery("#title_tutor").css("display", "none");
            jQuery("#datos_tutor").css("display", "none");
            jQuery("#doc_tutor").css("display", "none");
        }
    });

    if (jQuery('#rt_form_libro').length) {
        
        jQuery("#rt_form_libro").validate({
            rules:
                    {
                        flag_menor: {required: true},
                        acepto: {required: true},
                        politica: {required: true}
                    },
            messages: {
                nombres: "Ingrese su nombre",
                paterno: "Ingrese su apellido paterno",
                materno: "Ingrese su apellido materno",
                tipo_doc: "Ingrese tipo de documento",
                nro_doc: "Ingrese número de documento",
                cel: "Ingrese su celular",
                dep: "Ingrese deparamento",
                prov: "Ingrese provincia",
                dist: "Ingrese distrito",
                direccion: "Ingrese su dirección",
                correo: "Ingrese su correo",
                nro_pedido: "Ingrese número de pedido",
                tienda: "Ingrese tienda",
                tipo_reclamo: "Ingrese tipo de reclamo",
                tipo_consumo: "Ingrese tipo de reclamo",
                fch_reclamo: "Ingrese fecha de reclamo",
                descripcion: "Ingrese descripción del producto/servicio",
                fch_compra: "Ingrese fecha de compra",
                detalle_reclamo: "Ingrese detalle del reclamo",
                pedido_cliente: "Ingrese su pedido",
                flag_menor: "¿Eres menor de edad?",
                acepto: "Campo obligatorio ",
                politica: "Campo obligatorio"

            }
        });
    }

    jQuery('#referencia').keyup(function() {
        mayus(this);
    });

    function mayus(e) {
        e.value = e.value.toUpperCase();
    }

});
