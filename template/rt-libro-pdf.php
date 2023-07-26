<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Libro de reclamaciones</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: "Myriad Pro", Arial, Helvetica, sans-serif;
        }
        td, th {
            padding-left: 10px;
            padding-right: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .pr-5 {
            padding-right: 90px;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
        .mb-0 {
            margin-bottom: 0;
        }
        .mt-0 {
            margin-top: 0;
        }

        table.invoice-detail tbody tr:nth-child(odd) {
            background-color:#f2f2f2;
        }

        table.invoice-detail tbody tr:nth-child(even) {
            background-color:#fbfbfb;
        }
        .bg-gray-light {
            background-color:#f2f2f2;
        }
        .bg-gray-light-1 {
            background-color:#fbfbfb;
        }
        .logo {
            margin-bottom: 20px;
        }
        .text-gray {
            color: #585858;
        }
        .fs-14 {
            font-size: 14px;
        }

        .fs-12 {
            font-size: 12px;
        }

        .fs-10 {
            font-size: 10px;
        }

        .bg-gray{
            background: #CCC;
        }
        .pagenum {
            position: absolute;
            bottom: 0;
            left: 10px;
            width: 100%;
            text-align: center;
        }
        .pagenum:before {
            font-family: "Myriad Pro", Arial, Helvetica, sans-serif;
            font-size: 10px;
            content: "Página " counter(page);
        }
        table {
            border-collapse: collapse;
            font-size: 12px;
        }
        td {
            height: 25px;
            vertical-align: middle;
        }
    </style>
</head>
<body style="padding: 20px;">
<table width="100%" border="1" style="margin-top: 10px;">
    <tbody>
    <tr>
        <td style="color: #000000; text-align: center;" colspan="3">
            LIBRO DE RECLAMACIONES
        </td>
        <td rowspan="2" colspan="2">
            <table border="0" width="100%">
                <tbody>
                <tr>
                    <td colspan="2" style="color: #000000; text-align: center;">HOJA DE RECLAMACIÓN</td>
                </tr>
                <tr>
                    <td colspan="2" style="color: #e70011; text-align: center;">
                        Nº 00<?php echo $libro_id; ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 0;" colspan="3">
            <table width="100%" border="1" style="border-top: none; border-bottom: none;">
                <tbody>
                <tr>
                    <td style="color: #000000;">FECHA</td>
                    <td><?php echo date('d', strtotime($libro_data["fch_reclamo"])); ?></td>
                    <td><?php echo date('m', strtotime($libro_data["fch_reclamo"])); ?></td>
                    <td><?php echo date('Y', strtotime($libro_data["fch_reclamo"])); ?></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="color: #000000;" bgcolor="#c3c3c3" colspan="5">
            <b>Identificación del consumidor reclamante</b>
        </td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2"><b>NOMBRE:</b></td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["nombre"] . " " . $libro_data["apellido_paterno"] . " " . $libro_data["apellido_materno"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">DIRECCIÓN:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["direccion"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">REF DIRECCIÓN:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["referencia"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%">TIPO DOCUMENTACIÓN:</td>
        <td style="color: #000000;" width="20%"><?php echo  rt_libro_lrq_get_type_doc($libro_data['tipo_doc']) ?></td>
        <td style="color: #000000;" width="10%">NRO DOCUMENTACIÓN:</td>
        <td style="color: #000000;" width="20%" colspan="2"><?php echo $libro_data["nro_documento"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">¿Eres menor de edad?:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo ($libro_data["flag_menor"] == '1') ? 'Si': 'No' ?></td>
    </tr>
    <?php if($libro_data["flag_menor"]=='1'){ ?>
        <tr>
            <td style="color: #000000;" width="50%" colspan="2">Nombre PADRE, MADRE O TUTOR:</td>
            <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["nombre_tutor"] ?></td>
        </tr>
        <tr>
            <td style="color: #000000;" width="50%" colspan="2">Email PADRE, MADRE O TUTOR:</td>
            <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["email_tutor"] ?></td>
        </tr>
        <tr>
            <td style="color: #000000;" width="50%" colspan="2">Tipo Documento PADRE, MADRE O TUTOR:</td>
            <td style="color: #000000;" colspan="3" width="50%"><?php echo rt_libro_lrq_get_type_doc($libro_data['tipo_doc_tutor']) ?></td>
        </tr>
        <tr>
            <td style="color: #000000;" width="50%" colspan="2">Nro Documento PADRE, MADRE O TUTOR:</td>
            <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["numero_documento_tutor"] ?></td>
        </tr>
    <?php }  ?>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2"><b>DEPARTAMENTO:</b></td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["departamento"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">PROVINCIA:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["provincia"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">DISTRITO:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["distrito"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">TELÉFONO:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["fono"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;" width="50%" colspan="2">E-MAIL:</td>
        <td style="color: #000000;" colspan="3" width="50%"><?php echo $libro_data["email"] ?></td>
    </tr>

    <tr>
        <td style="color: #000000;" bgcolor="#c3c3c3" colspan="5">
            <b>Detalle del reclamo y orden del consumidor </b>
        </td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> TIPO RECLAMO:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo ($libro_data['tipo_reclamacion'] == 1 ? __('Claim', 'rt-libro') : __('Complain', 'rt-libro')) ?></td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> TIPO CONSUMO:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo  ($libro_data['tipo_consumo']==1 ? __('Product', 'rt-libro') : __('Service', 'rt-libro')) ?></td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> NRO DE PEDIDO:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo $libro_data["nro_pedido"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> PROVEEDOR:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo $libro_data["proveedor"] ?></td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> MONTO DE RECLAMO:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo $libro_data["monto_reclamado"] ?></td>
    </tr>
    <tr>
        <td colspan="5" style="color: #000000; height: 120px; vertical-align: top;">
            DESCRIPCIÓN: <?php echo $libro_data["descripcion"] ?>
        </td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> FECHA DE COMPRA:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo date("d/m/Y", strtotime($libro_data['fch_compra'])) ?></td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> FECHA DE CONSUMO:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo date("d/m/Y", strtotime($libro_data['fch_consumo'])) ?></td>
    </tr>
    <tr>
        <td style="color: #000000;"width="50%" colspan="2"> FECHA DE VENCIMIENTO:</td>
        <td style="color: #000000;" width="50%" colspan="3"><?php echo date("d/m/Y", strtotime($libro_data['fch_vencimiento'])) ?></td>
    </tr>
    <tr>
        <td colspan="5" style="color: #000000; height: 120px; vertical-align: top;">
            DETALLE: <?php echo $libro_data["detalle"] ?>
        </td>
    </tr>
    <tr>
        <td colspan="4" style="color: #000000; height: 80px; vertical-align: top;">
            PEDIDO: <?php echo $libro_data["pedido_cliente"] ?>
        </td>
        <td style="color: #000000; vertical-align: bottom; height: 80px;">
            ______________________
            FIRMA DEL CONSUMIDOR
        </td>
    </tr>

    <tr>
        <td style="color: #000000;" bgcolor="#c3c3c3" colspan="5">
            <b>4. OBSERVACIONES Y ACCIONES ADOPTADAS POR EL PROVEEDOR</b>
        </td>
    </tr>
    <tr>
        <td style="color: #000000;">FECHA DE COMUNICACIÓN DE LA RESPUESTA:</td>
        <td style="color: #000000;"></td>
        <td style="color: #000000;"></td>
        <td style="color: #000000;"></td>
        <td style="color: #000000; vertical-align: bottom; height: 80px;" rowspan="2">
            ______________________
            FIRMA DEL CONSUMIDOR
        </td>
    </tr>
    <tr>
        <td colspan="3" style="color: #000000; height: 80px; vertical-align: top;">
            DETALLE:
        </td>
    </tr>
    <tr>
        <td style="color: #000000; font-size: 10px; text-align:center;" colspan="2" width="50%">
            1. RECLAMO: Disconformidad relacionada a los productos<br>y servicios
        </td>
        <td style="color: #000000; font-size: 10px; text-align:center;" colspan="3" width="50%">
            2. QUEJA: Disconformidad no relacionada a los productos o servicios; o malestar o descontento respecto a la atención al público.
        </td>
    </tr>
    <tr>
        <td colspan="5" style="height: 20px;"></td>
    </tr>
    </tbody>
</table>
<p style="font-size: 11px; color: #000000">La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia a INDECOPI. El proveedor deberá dar respuesta al reclamo en un plazo no mayor a quince (15) días hábiles improrrogables.</p>

<span class="pagenum"></span>
</body>
</html>
