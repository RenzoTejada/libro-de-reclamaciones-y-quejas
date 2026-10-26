=== Libro de Reclamaciones y Quejas ===
Contributors: renzotejada, huakotech, alexistejada
Tags: libro de reclamaciones, libro de reclamaciones peru, libro de reclamaciones wordpress, libro de reclamaciones plugin
Requires at least: 6.2
Tested up to: 6.8.1
Stable tag: 1.3.21
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Libro de reclamaciones válido para Perú con los campos obligatorios exigidos por Indecopi.

== Description ==

* Email the customer with a copy of their complaint.
* You send an email to the site administrator with the complaint.
* The site administrator cannot delete or edit the information sent by the customer.
* The "Complaint Book" plugin generates a unique correlative ID in the database which makes it difficult to modify and edit.
* All complaints are viewed in the same WordPress dashboard in a clear way.
* WE ARE NOT RESPONSIBLE FOR COMPLAINTS THAT YOU MAY HAVE, ALWAYS CHECK YOUR CLAIMS BOOK WITH YOUR LEGAL AREA OR A LAWYER, AS NOT ALL COMPANIES THAT PROVIDE PRODUCTS OR SERVICES HAVE THE SAME FORMAT FIELDS.
* THIS PLUGIN IS A GENERIC PLUGIN THAT DOES THE BASICS (HAVE THE FORM ONLINE, NOTIFY THE ADMINISTRATOR AND THE USER OF A NEW CLAIM).

We also have a premium plugin which has the following features: 

* The plugin generates a unique correlative ID in the database which makes it difficult to modify and edit. 
* All complaints are seen in the same WordPress dashboard in a clear way. 
* You will be able to answer the claim or complaint from the same WordPress dashboard.
* You will be able to attach evidence of the complaint from within the WordPress dashboard. You will be able to activate Google reCaptcha v2.
* You will be able to change the email to notify when a complaint is created. 

More information about the plugin in [Libro de Reclamaciones y Quejas PRO](https://renzotejada.com/plugins/libro-de-reclamaciones-y-quejas-pro/ "Libro de Reclamaciones y Quejas PRO")

= View more plugins =

For additional functionality, check out our companion plugin, such as:

* [Ubigeo Perú](https://wordpress.org/plugins/ubigeo-peru/)
* [Libro de Reclamaciones y Quejas](https://wordpress.org/plugins/libro-de-reclamaciones-y-quejas/)
* [Comprobante de Pago Perú](https://wordpress.org/plugins/comprobante-de-pago-peru/)
* [Tipo Documento Perú](https://wordpress.org/plugins/tipo-documento-peru/)
* [Transferencia Bancaria Perú](https://wordpress.org/plugins/transferencia-bancaria-peru/)
* [Utils para WooCommerce y WordPress](https://wordpress.org/plugins/wp-utils/)
* [Multi Link in Bio](https://wordpress.org/plugins/multi-link-in-bio/)
* [Display Price Free](https://wordpress.org/plugins/display-price-free/)

= View more plugins PREMIUM =

* [Costo de envío de Ubigeo Perú](https://renzotejada.com/plugins/costo-de-envio-de-ubigeo-de-peru-para-woocommerce/)
* [Libro de Reclamaciones y Quejas PRO](https://renzotejada.com/plugins/libro-de-reclamaciones-y-quejas-pro/)
* [WooYape para WooCommerce](https://renzotejada.com/plugins/wooyape-para-woocommerce/)
* [WooLukita para WooCommerce](https://renzotejada.com/plugins/woolukita-para-woocommerce/)
* [WooPlin para WooCommerce](https://renzotejada.com/plugins/wooplin-para-woocommerce/)
* [WooTunki para WooCommerce](https://renzotejada.com/plugins/wootunki-para-woocommerce/)
* [WooBilletera para WooCommerce](https://renzotejada.com/plugins/woobilletera-para-woocommerce/)
* [Comprobante de Pago Perú PRO para WooCommerce](https://renzotejada.com/plugins/comprobante-de-pago-peru-pro-para-woocommerce/)


Visit our [plugins overview page](https://renzotejada.com/categoria-producto/plugins/) for more information.

== Installation ==

Instalación automática
1. Panel de administración plugin y añadir nueva opción.
2. Buscar en el cuadro de texto-región-provincia común.
3. Colocado en la descripción de este plugin y seleccione instalar.
4. Activar el plugin desde el panel de administración de WordPress.

Archivos ZIP Manual de instalación
1. Descargar el archivo ZIP desde esta pantalla.
2. Seleccione la opción agregar plugin desde el panel de administración.
3. Seleccione la opción de alta carga y seleccione el archivo descargado.
4. Confirmar Instalación y plugin de activación desde el panel de administración.

Instalación manual de FTP
1. Descargue el archivo ZIP de esta pantalla y descomprimir.
2. Ingrese a su carpeta FTP en su servidor web.
3. Copie el directorio libro-de-reclamaciones-y-quejas dentro de la siguiente direccion  wp-content/plugins/
4. Activar el plugin desde el panel de administración de WordPress.


== Changelog ==

= 1.3.21 (22/10/2026) =
* PDF: paleta neutral monocromática (blanco, negro y grises) en encabezados, recuadro de registro, secciones y tablas, sin cambiar los datos ni el formato A4.
* Email HTML: encabezado corporativo con el logotipo PNG/JPEG configurado para el establecimiento, incrustado como imagen CID; si no existe logo, mostrar el nombre comercial.
* Email HTML: cabecera de confirmación en tonos neutros y sin verde predeterminado.
* Se preservan los adjuntos PDF, el identificador correlativo interno y los controles de seguridad de los PDF temporales de la versión 1.3.20.
* Se conserva el historial completo de cambios anterior.


= 1.3.20 (21/10/2026) =
* Rediseño del encabezado y distribución de la hoja de reclamación PDF, con marca e información de empresa compactas.
* Nombres PDF aleatorios, independientes del número correlativo de reclamación.
* Los PDF nuevos se generan temporalmente fuera de uploads y se eliminan tras enviar el correo con el adjunto.
* Se mantiene el número oficial de reclamación dentro del documento y en el correo.
* Seguridad: las copias históricas de /wp-content/uploads/libro-pdfs/ no se eliminan automáticamente; el administrador debe retirarlas o protegerlas, especialmente si el servidor es Nginx.


= 1.3.19 (21/10/2026) =
* Incluye en el correo HTML al cliente y la copia interna una sección «Declaraciones y autorizaciones».
* Muestra el texto de la declaración jurada y su estado real (Sí/No), registrado en acepta_contenido.
* Muestra el texto configurable de aceptación de privacidad y cookies y su estado real (Sí/No), registrado en acepta_politica.
* Escapa los valores y no presupone que las casillas estuvieran marcadas; conserva formulario, PDF adjunto y configuración de destinatarios.
* Mantiene intacto el historial de cambios previo.


= 1.3.18 (21/10/2026) =
* Convierte el correo de recepción a HTML responsive con tablas compatibles con clientes de correo (Gmail, Outlook y móviles).
* Destaca el número de registro e incluye secciones de establecimiento, consumidor y detalle de reclamo o queja.
* Conserva PDF adjunto y destinatario interno mediante CC; configura Content-Type text/html solo para este envío.
* Escapa los valores del formulario en HTML y omite campos opcionales vacíos.
* Conserva el historial completo de versiones anteriores.


= 1.3.17 (21/10/2026) =
* Mejora el mensaje de confirmación tras registrar correctamente un reclamo o queja: tarjeta destacada, icono, explicación breve y número de registro fácil de identificar.
* Mantiene el correlativo existente, la accesibilidad del mensaje y la lógica de registro sin cambios.
* Los estilos nuevos se aplican exclusivamente al mensaje de éxito, sin alterar los campos del formulario.
* Se conserva íntegro el historial de versiones anteriores.


= 1.3.16 (21/10/2026) =
* Restaura explícitamente el borde inferior del select que algunos temas dejan invisible.
* No cambia el HTML, el flujo del formulario, la lógica PHP ni los demás bordes y estilos del tema.

= 1.3.15 (21/10/2026) =
* Corrige el recorte vertical de los selectores en temas que les aplican un alto insuficiente.
* Normaliza solo alto, altura de línea y espaciado vertical de los selectores dentro del formulario (40px como los campos de texto), manteniendo los estilos visuales del tema.
* Conserva la maquetación, validaciones y comportamiento de la versión anterior.


= 1.3.14 (21/10/2026) =
* Estructura accesible para los selectores: etiquetas label asociadas y desplegables en su propia línea.
* Los selectores ocupan su columna sin imponer fondo, borde, colores, padding ni alturas: el tema conserva la apariencia visual.
* Retirados tabindex negativo y aria-hidden de controles visibles para permitir uso con teclado y lectores de pantalla.

= 1.3.13 (21/10/2026) =
* Compatibilidad: elimina totalmente las reglas CSS dirigidas a los elementos select del formulario público para dejar su presentación al tema activo.
* Conserva la estructura de filas, campos, validaciones y funcionalidad del plugin sin cambios.


= 1.3.12 (20/10/2026) =
* Fix: restablece la alineación de las filas del formulario aislando sus columnas flotantes.
* Fix: elimina los cambios intrusivos de altura, fuente y padding de los selectores de 1.3.11; conserva el estilo del tema y evita desbordamiento horizontal.
* Mantiene los datos de empresa con estilos neutros y el formulario manual sin campos separados de ubicación.

= 1.3.11 (20/10/2026) =
* UX: el bloque opcional de datos empresariales adopta los colores y tipografía del tema y elimina el borde/fondo propios.
* Compatibilidad: los selectores de formulario utilizan altura mínima, padding para flecha nativa y ancho adaptable a temas y dispositivos.
* Se conservan datos, opciones, PDF, correos y shortcode existentes.

= 1.3.10 (20/10/2026) =
* Validación por campo de datos empresariales: nombre comercial, razón social, RUC, dirección, teléfono y correo.
* La razón social debe incluir una letra; el RUC exige 11 dígitos; el correo solo admite una dirección.
* Si se activa el encabezado empresarial, se requiere razón social, RUC y dirección.
* Los campos son opcionales cuando el encabezado está oculto. Los errores no borran los datos enviados ni causan guardado parcial.

= 1.3.9 =
* Conservar el borrador de configuración cuando falle una validación. Comprobación del RUC antes de guardar.

= 1.3.8 =
* Datos empresariales separados: nombre comercial, razón social, RUC, dirección, teléfono, correo y logo.
* Encabezado empresarial opcional y oculto por defecto; integración de los datos disponibles en constancias.

= 1.3.7 =
* Ubicación manual mediante un único campo Dirección; se ocultan departamento, provincia y distrito.

= 1.3.6 =
* Ajustes de maquetación del formulario simplificado y omisión de campos vacíos en PDFs y correos.

= 1.3.5 =
* Opciones persistentes para formulario completo/simplificado y ubicación manual/Ubigeo Perú.

= 1.3.4 =
* Destinatario de notificaciones editable: una dirección de correo, independiente del administrador de WordPress.

= 1.3.3 =
* Banner administrativo actualizado para describir registro, seguimiento interno y control de plazos.

= 1.3.2 =
* Nuevo encabezado gráfico responsive en el administrador.

= 1.3.1 =
* Control preventivo de conflicto con la edición Pro durante la activación de Free.

= 1.3.0 =
* Unificación de los filtros del listado de reclamos con los contadores del panel.

= 1.2.9 =
* Corrección de los destinos de los enlaces de Addons.

= 1.2.8 =
* Mejora de la paginación y limpieza de enlaces duplicados en la fila del plugin.

= 1.2.7 =
* Pestaña Addons y enlaces de promoción con parámetros UTM; ocultar la promoción Pro cuando corresponda.

= 1.2.6 =
* Enlaces de autor y a edición Pro en la lista de plugins.

= 1.2.5 =
* Historial completo visible por defecto, con filtro mensual opcional y paginación.

= 1.2.4 =
* Mejoras visuales para configuración y detalle administrativo del reclamo.

= 1.2.3 =
* Dashboard con contadores, búsquedas, estados administrativos, paginación y plazos estimados.
* Aviso: los plazos son orientativos hasta configurar feriados aplicables.

= 1.2.2 =
* Texto de aceptación de políticas editable y enlaces en línea con marcadores.

= 1.2.1 =
* Prevent duplicate submissions on refresh (HTTP 303, unique submission identifier and nonce).

= 1.2 (03/06/2025) =
Fix: Error de despliegue del plugin

= 1.1 (03/06/2025) =
Fix: Corrigiendo error de instalacion setup

= 1.0 (01/06/2025) =
Fix: Removiendo template de libro (incompatibilidad con los temas)
Fix: refactorizando codigo
Fix: validando versión WC 9.9.0
Fix: validando versión WP 6.8.1

= 0.9 (10/03/2025) =
Fix: Agregando nota.

= 0.8 (10/03/2025) =
Fix: Quitando retrincción de obligacion de politicas de cookies.
Fix: validando versión WC 9.7.1
Fix: validando versión WP 6.7.2

= 0.7 (16/01/2025) =
Fix: ignorando archivos

= 0.6 (16/01/2025) =
Fix: validando versión WC 9.5.2
Fix: validando versión WP 6.7.1

= 0.5 (15/11/2024) =
Fix: validando versión WC 9.4.1
Fix: validando versión WP 6.7

= 0.4 (10/11/2024) =
Fix: validado version WP 6.6.2
Fix: validado versión WC 9.3.3
Fix: error de menu al mostrar detalle de libro

= 0.3 (01/09/2024) =
Fix: validado versión WC 9.2.3
Fix: validado versión WP 6.6.1

= 0.2.0 (04/04/2024) =
Fix: validado versión WC 8.7.0
Fix: validado versión WP 6.5

= 0.1.9 (22/03/2024) =
Fix: validado versión WC 8.7.0
Fix: validado versión WP 6.4.3

= 0.1.8 (26/07/2023) =
Fix: Se agrega la funcionalidad de generación de pdf
Fix: Se envía la misma notificación al admin como al cliente.
Fix: validado versión WC 7.9.0
Fix: validado versión WP 6.3

= 0.1.7 (06/12/2022) =
Fix: validado versión WC 7.1.0
Fix: validado versión WP 6.1.1

= 0.1.6 (09/11/2022) =
Fix: validado versión WC 7.1
Fix: validado versión WP 6.1
Fix: Cambiando el texto de libro de 30 dias a 15 dias de contestación.

= 0.1.5 (16/05/2022) =
Fix: validado versión WC 6.5.1
Fix: validado versión WP 6.0

= 0.1.4 (14/06/2021) =
Fix: validado versión WC 5.4.1.
Fix: agregand links en el readme de plugins free y premium.

= 0.1.3 (12/05/2021) =
Validado en la versión WP 5.7.2

= 0.1.2 (12/05/2021) =
Validado en la versión WC 5.3.0

= 0.1.1 =
Agregando la copia del formulario en el email.

= 0.1.0 =
Correciones de notice en el admin y en la vista de libro de reclamaciones del shortcode.

= 0.0.2 =
Rename del template para evitar conflictos con los templates de los diferentes temas en wp.

= 0.0.1 =
Versión de lanzamiento del plugin Libro de Reclamaciones y Quejas.

