var tourLogo  = 'ExpoFinder';
var tourTemplate = '<div class="popover tour"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div><div class="popover-navigation"><button class="btn btn-default" data-role="prev"><span class="glyphicon glyphicon-backward"></span></button><span data-role="separator">&nbsp;</span><button class="btn btn-default" data-role="next"><span class="glyphicon glyphicon-forward"></span></button><span data-role="separator">&nbsp;</span><button class="btn btn-default" data-role="end"><span class="glyphicon glyphicon-stop"></span></button></div></div>';
  
var tourSteps = [
    {
        path: "",
        element: "#tour-ef",
        placement: "bottom",
        title: "Bienvenido a la aplicación " + tourLogo,
        content: "La visita guiada le permitirá conocer el funcionamiento de cada elemento del interfaz de usuario.\nUtilice los botones <span class=\"glyphicon glyphicon-backward\"></span>, <span class=\"glyphicon glyphicon-forward\"></span> o <span class=\"glyphicon glyphicon-stop\"></span> para retroceder un paso, avanzar al paso siguiente o detener la visita guiada."
    },
    {
        path: "",
        element: "#menu-paginas-estaticas",
        placement: "bottom",
        title: "Un menú único para acceder a todas partes",
        content: "En la barra de la zona superior siempre se muestra este <em>menú general</em>, donde podrá encontrar las opciones principales, agrupadas en tres grandes bloques:<ul><li><strong>Proyectos</strong>, con una información textual sobre cuáles son los proyectos implicados en " + tourLogo + " y cuál es su estado.</li><li><strong>Documentación</strong>, con información técnica sobre la estructura interna de la aplicación.</li><li><strong>Consultas</strong>, con los elementos activos que constituyen el núcleo funcional de " + tourLogo + "</li></ul>"
    },
    {
        path: "",
        element: "#tour-user-menu",
        placement: "bottom",
        title: "Identificación de usuario",
        content: "También en la barra de la zona superior siempre se muestra este <em>menú de usuario</em>, que ofrece información sobre su identidad en " + tourLogo + ", su desconexión del sistema y las estadísticas del proceso de evaluación."
    },
    {
        path: "",
        element: ".footer",
        placement: "top",
        title: "Barra de estado",
        content: "La zona inferior de la pantalla contiene información sobre el estado actual de " + tourLogo + ". Siempre está presente."
    },
    {
        path: "",
        element: "#tour-stats",
        placement: "top",
        title: "Información Estadísticas de funcionamiento",
        content: "En las tres etiquetas sobre fondo gris se indica el número de consultas efectuadas a la base de datos para acceder a la página actualmente activa, el tiempo transcurrido (en segundos) para generar y mostrar su contenido y el estado de la memoria del sistema, con el total disponible (ambos datos en MB)."
    },
    {
        path: "",
        element: "#tour-legal",
        placement: "top",
        title: "Información legal",
        content: "Pulsando aquí accederá a una página donde se informa de las condiciones legales de acceso, la titularidad de la aplicación y otros datos relevantes a efectos normativos."
    },
    {
        path: "",
        element: "#evallink",
        placement: "top",
        title: "Evaluación de la página",
        content: "Permmite valorar la calidad de la página que se muestra. El símbolo <span class=\"dashicons dashicons-awards\" style=\"color: #d9534f;\"></span> indica que aún no se ha efectuado la evaluación para la página actual. Consulte <a href=\"/manuales\">los manuales de funcionamiento</a> al respecto delproceso evaluador."
    },
    {
        path: "",
        element: "#tour-bug",
        placement: "top",
        title: "Informe de errores",
        content: "Envía a la administración de " + tourLogo + " un informe detallado sobre cualquier error que se haya producido al acceder a una página o durante el funcionamiento de la misma. Utilícelo siempre que sea necesario."
    },
    {
        path: "/manuales",
        element: ".page-header",
        placement: "bottom",
        title: "Manuales de la aplicación",
        content: "Dispone de una completa documentación orientativa y descriptiva sobre el funcionamiento del sistema. No dude en consultarla siempre que sea preciso."
    },
    {
        path: "/formulario-de-consulta",
        element: ".page-header",
        placement: "bottom",
        title: "Formulario de consulta",
        content: "Puede plantear cualquier duda o solicitud de información a la administración de " + tourLogo + " enviando (por correo electrónico y de forma automática) este formulario. Sea claro y lo más explícito posible. Recibirá una respuesta a la mayor brevedad posible."
    },
    {
        path: "/",
        element: "#tour-home-link",
        placement: "bottom",
        title: "Un buen comienzo",
        content: "La aplicación se inicia en la página de <em>portada</em>, a la que se accede pulsando aquí. En ella se ofrece un breve resumen del estado del proyecto y de el número de datos almacenados, además de cierta información de interés estadístico sobre vínculos entre registros, distribución geográfica, etcétera."
    },
    {
        path: "/banco-de-datos",
        element: ".page-header",
        placement: "bottom",
        title: "Banco de datos",
        content: "El <em>banco de datos</em> permite consultar cualquier referencia introducida en " + tourLogo + " y sus detalles concretos. Tenga en cuenta que no se muestran todos los almacenados en el sistema, sino solamente aquellos cuyo carácter público permita su difusión.",
        reflex: true
    }, 
    {
        path: "/banco-de-datos",
        element: "#tour-searchform .panel-body",
        placement: "top",
        title: "Búsqueda sencilla y eficaz de información en el banco de datos",
        content: "Utilizando este formulario podrá localizar cuanta información desee. Puede establecer filtros por tipo de registro almacenado utilizando el botón desplegable situado a la derecha.",
    }, 
    {
        path: "/banco-de-datos",
        element: "#tour-searchform",
        placement: "bottom",
        title: "Listado de hallazgos",
        content: "Cada registro se muestra con expresión de su título y una breve indicación de su contenido, además de la tipología, el usuario verificador y la fecha de grabación. Pulse sobre el título para acceder a la ficha de detalle.",
    }, 
    {
        path: "/analisis",
        element: ".page-header",
        placement: "bottom",
        title: "La herramienta más potente",
        content: "La página de <em>análisis</em> es la herramienta más importante de " + tourLogo + ". No dude en consultar los <a href=\"/manuales\">los manuales de funcionamiento</a> para tratar de obtener de ella el máximo rendimiento.",
    },
    {
        path: "/analisis",
        element: ".nav-tabs",
        placement: "bottom",
        title: "Tres soluciones de trabajo",
        content: "Utilice cualquiera de las diversas opciones que la página de <em>análisis</em> le ofrece: <strong>tabla de datos</strong>, <strong>tabla dinámica</strong> y, en su caso, <strong>gráfico resumen</strong>. Para hacerlo, pulse sobre la correspondiente pestaña.",
        backdrop: true,
        backdropPadding: 5
    },
    {
        path: "/analisis",
        element: ".well-sm",
        placement: "bottom",
        title: "Selección de consulta",
        content: tourLogo + " ofrece un grupo de consultas predefinidas que permiten obtener información para extraer conocimiento a partir de su análisis. En los <a href=\"/manuales\">los manuales de funcionamiento</a> dispone de una detallada descripción acerca de ellas. Para realizar cualquier <em>análisis</em> seleccione la consulta que desee y pulse <em>Ejecutar</em>.",
        backdrop: true,
        backdropPadding: 5
    },
    {
        path: "/analisis",
        element: ".tab-content #table",
        placement: "top",
        title: "Tablas de datos",
        content: "Son un excelente medio de recuperar gran cantidad de información de " + tourLogo + ". Podrá exportarla a diferentes formatos, filtrarla y ordenarla como desee.",
        backdrop: true,
        backdropPadding: 5
    },
    {
        path: "/analisis",
        element: ".tab-content #pivot",
        placement: "top",
        title: "Tablas dinámicas",
        content: "Con diferencia, constituyen la herramienta más versátil y potente de " + tourLogo + ". Aprenda a usarla consultando al respecto los <a href=\"/manuales\">los manuales de funcionamiento</a>.",
        backdrop: true,
        backdropPadding: 5
    },
    {
        path: "/analisis",
        element: ".tab-content #chart",
        placement: "top",
        title: "Gráfico resumen",
        content: "Aunque no siempre están disponibles, puesto que dependen de la naturaleza de la consulta ejecutada, los <em>gráficos resumen</em> son una manera visualmente atractiva e intuitiva de percibir los datos más destacables de los registros recuperados.",
        backdrop: true,
        backdropPadding: 5
    },
    {
        path: "/",
        title: "y ahora&hellip; ¡a trabajar!",
        content: tourLogo + " ofrece todo un mundo de posibilidades para conocer, trabajar y valorar, empleando el paradigma digital de aproximación a la disciplina de Historia del Arte, las exposiciones temporales. Aprovéchelo. Y no dude en contactar con nosotros para resolver cualquier duda.",
        orphan: true,
        onHidden: function() {
            return window.location.assign("/");
        }
    },
];

var tourEnded = '<div class="alert alert-info alert-dismissable">' + 
    '<button class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + 
    'La visita guiada ha finalizado. <a href="#" data-demo>Reinicisr la visita.</a>' + 
    '</div>';