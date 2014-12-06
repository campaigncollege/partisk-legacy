/**
 * Copyright 2013-2014 Partisk.nu Team
 * https://www.partisk.nu/
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @copyright   Copyright 2013-2014 Partisk.nu Team
 * @link        https://www.partisk.nu
 * @package     app.webroot.js
 * @license     http://opensource.org/licenses/MIT MIT
 */

var datepickerArgs = {autoclose: true, format: 'yyyy-mm-dd', language: "sv", calendarWeeks: true, endDate: new Date()};
var bigMode = matchMedia('only screen and (min-width: 463px)').matches;

$(document).ready(function() {
    $('.pop').popover();
    $('.modal').on('shown.bs.modal', function() {
        $(this).find("input:visible").first().focus();
    });
    $('.datepicker').datepicker(datepickerArgs);

    if ($('.qa-table').size() > 0) {
        qaTableFixedHeader();
    }

    $('#partisk-search input').typeahead([
        {
            name: 'questions',
            remote: appRoot + 'fr%C3%A5gor/search/%QUERY',
            minLength: 3
        }
    ]).bind('typeahead:selected', function(event, obj) {
        if (obj.key) {
            window.location = appRoot + "fr%C3%A5gor/" + encodeString(obj.value);
        }

        $(this).val("");
    }).focus();
       
    attachAccordions($('#accordion'));
    
    if (!supportsSvg()) {
        $("#graphs").hide();
        $("#no-svg").show();
    }
    
    initPopovers($("body"));

    $('body').on('click', function(e) {
        $('.popover.in').prev().not(e.target).not($(e.target).parent()).popover('toggle');
    });

    // Open modal without fade if it contains an error
    $('.modal').each(function(index, modal) {
        if ($(modal).find('p.error').size() > 0 || $(modal).hasClass('open-modal-on-load')) {
            $(modal).removeClass('fade');
            $(modal).on('shown.bs.modal', function() {
                $(this).addClass('fade in');
                $('.modal-backdrop').addClass('fade in');
            });
            $(modal).modal('show');

        }
    });
});

var attachAccordions = function (accordion) {
    accordion.find('.panel-collapse').on('show.bs.collapse', function () {
       $(this).parent().find(".toggle").removeClass("fa-plus-square").addClass("fa-minus-square");
       $(this).parent().addClass("accordion-expanded");
       $(this).find('.table-head-container').removeClass('table-fixed').show();
       $(this).find('.table-with-fixed-header').removeClass('table-fixed-header');
    });

    accordion.find('.panel-collapse').on('hide.bs.collapse', function () {
       $(this).parent().find(".toggle").removeClass("fa-minus-square").addClass("fa-plus-square");
       $(this).parent().removeClass("accordion-expanded");
    });
    
    accordion.find('.panel-collapse.ajax-load-table').on('show.bs.collapse', function () {
       var id = $(this).attr('data-id');
       var type = $(this).attr('data-type');
       var container = $(this);
       
       var method = type === "category" ? 'getCategoryTable' : 'getQuestionSummaryTable';
       
       if (!container.hasClass('table-loaded')) {
       $.ajax({
            url: appRoot + 'frågor/' + method + '/' + id,
            success: function(data) {
                var content = $(data);
                content.hide();
                container.append(content);
                
                if (type === "category") {
                    setupFixedHeader(content);
                }
                
                initPopovers(container);
                content.fadeIn('slow');
                container.addClass('table-loaded');
            }
        });
        }
    });
}

var encodeString = function(str) {
    return encodeURI(str.split(' ').join('_').toLowerCase()).replace('?', '%3f');
};

var initPopovers = function($container) {
    $container.find('.popover-hover-link').popover({
        html: true,
        placement: "auto",
        trigger: 'hover',
        content: function() {
            return $(this).next('.popover-data').html();
        }
    });
    
    $container.find('.popover-click-link').popover();

    $container.find('.popover-link').bind('click', function() {
        var $popover = $(this);
        $.ajax({url: appRoot + "answers/info/" + $popover.attr('data-id'), success: function(data) {
                $popover.unbind('click');
                $popover.popover({
                    html: true,
                    placement: "auto",
                    content: function() {
                        return data;
                    }
                }).popover('show');
            }});
    });
    
    $container.find('.empty-answer-popover').bind('click', function() {
        var $popover = $(this);
        $.ajax({url: appRoot + "questions/empty_answer/" + $popover.attr('data-question-id') + "/" 
                    + $popover.attr('data-party-id'), success: function(data) {
                $popover.unbind('click');
                $popover.popover({
                    html: true,
                    placement: "auto",
                    content: function() {
                        return data;
                    }
                }).popover('show');
            }});
    });
}

$(window).resize(function() {
    newBigMode = matchMedia('only screen and (min-width: 463px)').matches;
    
    if (bigMode !== newBigMode) {
        bigMode = newBigMode;
        
        if (bigMode) {
            qaTableFixedHeader();
        }
    }
});

var qaTableFixedHeader = function() {
    if (matchMedia('only screen and (min-width: 463px)').matches && $('.table-head-container').size() === 0) {
        var tables = $('.table-with-fixed-header');
        
        tables.each(function (item) {
            setupFixedHeader($(this));
        });
    
    }
};

var setupFixedHeader = function (table) {
            var qaTableHead = $('<div class="table-head-container"></div>');
            var qaTableHeadRow = $('<div class="table qa-table table-bordered table-striped"></div>');
            var qaTableHeadBg = $('<div class="table-header-bg"></div>');

            qaTableHead.append(qaTableHeadBg);
            qaTableHead.append(qaTableHeadRow);

            table.before(qaTableHead);
            table.find('.table-head.table-row').clone().appendTo(qaTableHeadRow);
            var headerHeight = qaTableHeadRow.find('.table-row.table-head').height();
            //qaTableHeadRow.width(table.width());
            
            var faded = false;
            var headerVisible = false;
            $(window).scroll(function() {
                if (bigMode) {
                    var scrollTop = $(window).scrollTop();
                    if (!faded && scrollTop >= table.offset().top + table.height()) {
                        qaTableHead.fadeOut("fast", function () { faded = true; });
                    } else if (faded && scrollTop <= table.offset().top + table.height()) {
                        qaTableHead.fadeIn("fast", function () { faded = false; });
                    }
                    
                        //console.log($(window).scrollTop() + ">=" + (table.offset().top));
                    if (scrollTop >= table.offset().top) {
                        if (!headerVisible) {
                            headerVisible = true;
                            qaTableHead.addClass('table-fixed');
                            table.addClass('table-fixed-header');
                        }
                    } else {
                        if (headerVisible) {
                            headerVisible = false;

                            qaTableHead.removeClass('table-fixed');
                            table.removeClass('table-fixed-header');
                        }
                    }
                }
            });
};

var openModal = function(controller, action, parameters) {
    
    $.ajax({
        url: appRoot + controller + '/' + action + '/' + parameters.join("/"),
        success: function(data) {
            $modal = $(data);
            $("body").append($modal);
            $modal.modal();
            $modal.find('.datepicker').datepicker(datepickerArgs);

            $modal.on('hidden.bs.modal', function() {
                $modal.remove();
            });
        }
    });
};

// http://stackoverflow.com/questions/1026069/capitalize-the-first-letter-of-string-in-javascript
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getQuestionAgreeRate() {
    var result = {key: 'questionAgreeRate', values: []};

    var agree_rate = data["question_agree_rate"];

    for (var value in agree_rate) {
	if (parties[value] !== undefined) {
            result.values.push({value: agree_rate[value]['result'], range: agree_rate[value]['range'], plus_points: agree_rate[value]['plus_points'],
				label: capitalizeFirstLetter(parties[value].name), minus_points: agree_rate[value]['minus_points'],
				party_id: parties[value].id, short_label: parties[value].short_name,
				color: parties[value].color, order: parties[value].order});
	}
    }

    result.values.sort(function(a, b) {
        return a.order - b.order;
    });
    return [result];
}

function getPointsPercentage() {
    var result = {key: 'pointsPercentage', values: []};

    var points_percentage = data['points_percentage'];

    for (var value in points_percentage) {
        if (parties[value] !== undefined && points_percentage[value]['result'] > 0) {
            result.values.push({value: points_percentage[value]['result'], range: points_percentage[value]['range'],
                points: points_percentage[value]['points'], label: capitalizeFirstLetter(parties[value].name),
                party_id: parties[value].id, short_label: parties[value].short_name,
                color: parties[value].color, order: parties[value].order});
        }
    }

    result.values.sort(function(a, b) {
        return a.order - b.order;
    });
    return [result];
}


$(document).ready(function() {
    if (typeof data !== 'undefined' && data != null) {
        $("#quiz-summary").show();
        $("#graphs").show();
        
        if ($.cookie('p[q]') === quizId) {
            $.ajax({
                url: appRoot + "quiz/session_results/" + quizId,
                success: function(data) {
                    var container = $("#session-results");
                    var content = $(data);
                    content.hide();
                    container.append(content);
                    
                    attachAccordions(container);
                    
                    content.fadeIn('slow');
                }
        });
       }
        
        var items = [];
        
        for (party in parties) {
            var item = {plus: data.question_agree_rate[party].plus_points,
                minus: data.question_agree_rate[party].minus_points,
                party: party,
                agree_rate: data.question_agree_rate[party].result,
                percentage: data.points_percentage[party].result};
            items.push(item);
        }
        
        items.sort(function(a, b) {
            return (b.plus - b.minus) - (a.plus - a.minus);
        });
        
        for (current_item in items) {
            var item = items[current_item];
            var points = (item.plus - item.minus);
            $row = $('<tr class="' + (points > 0 ? "plus" : (points < 0 ? "minus" : "")) + '-points"></tr>');
            $row.append('<td><a href="' + appRoot + 'partier/' + encodeString(parties[item.party].name) + 
                        '" class="party-logo-link"><div class="party-logo-small party-logo-small-' + item.party + 
                        '"></div><div class="party-title">' + capitalizeFirstLetter(parties[item.party].name) + '</div></a></td>');
            $row.append("<td>" + item.plus + "p</td>");
            $row.append("<td>" + item.minus + "p</td>");
            $row.append('<td class="result"><span class="result">' + (points > 0 ? "+" : "") + points + 'p</span></td>');
            $row.append('<td class="percent">' + item.percentage + "%</td>");
            $("#result-table tbody").append($row);
        }
        
        

    nv.addGraph(function() {
        var data = getPointsPercentage();
        var chart = nv.models.pieChart()
                .x(function (d) {
                    return d.label;
                })
                .y(function (d) {
                    return d.value;
                })
                .tooltips(true)
                .margin({left: 1, top: 0, bottom: 10, right: 0})
                .color(function (item) {
                    if (item.data && item.data.color)
                        return item.data.color;
                    return "#333";
                })
                .labelThreshold(.06)
                .tooltipContent(function (key, value, item, graph) {
                    var result = '<h3>' + key + '</h3>' + '<p>' + Math.round(value) + '%</p>';
                    result += '<p>' + item.point.points + 'p' + '</p>';
                    return result;
                })
                .showLabels(true);

        d3.select("#points-percentage-graph svg")
                .datum(getPointsPercentage())
                .transition().duration(500)
                .call(chart);

        var bars = d3.select('#points-percentage-graph svg').selectAll('g.nv-label');

        if (!isInternetExplorer) {
            bars.append("foreignObject")
              .attr("width", 25)
              .attr("height", 25)
              .attr("y", function (d, i) { return -12; })
              .attr("x", function (d, i) { return -12; })
              .append("xhtml:body")
              .attr("style", "background-color: transparent")
              .attr("text-anchor", "middle")
              .html(function (d, i) { 
                  return data[0].values[i].points > 0 ? "<div class='party-logo-small party-logo-small-" + data[0].values[i].party_id + "'></div>" : null; 
              });
          }

        nv.utils.windowResize(chart.update);

        return chart;
    });

    nv.addGraph(function() {
        var data = getQuestionAgreeRate();
        var chart = nv.models.discreteBarChart()
                .x(function (d) {
                    return "(" + d.short_label + ")"; //"?"; //(d["short_label"] !== undefined ? "asd" : "?");
                })
                .y(function (d) {
                    return d.value;
                })
                .margin({left: 40, top: 5, bottom: 40, right: 0})
                .staggerLabels(false)
                .tooltips(true)
                .tooltipContent(function (id, key, value, item) {
                    var result = '<h3>' + item.point.label + '</h3>' + '<p>' + Math.round(value) + '%</p>';
                    result += '<p>För: ' + item.point.plus_points + 'p</p>';
                    result += '<p>Emot: ' + item.point.minus_points + 'p</p>';
                    return result;
                })
                .valueFormat(function (value) {
                    return Math.round(value) + "%";
                })
                .showValues(true);

        d3.select('#question-agree-rate-graph svg')
                .datum(data)
                .transition().duration(500)
                .call(chart);

        nv.utils.windowResize(chart.update);

        var bars = d3.select('#question-agree-rate-graph svg').selectAll('g.nv-x.nv-axis g.nv-wrap.nv-axis > g > g');

        if (!isInternetExplorer) {
            bars.append("foreignObject")
              .attr("width", 25)
              .attr("height", 25)
              .attr("y", function (d, i) { return 5; })
              .attr("x", function (d, i) { return -25/2; })
              .append("xhtml:body")
              .attr("style", "background-color: transparent")
              .attr("text-anchor", "middle")
              .html(function (d, i) { 
                  return "<div class='party-logo-small party-logo-small-" + data[0].values[i].party_id + "'></div>" 
              });
          } 

        return chart;
    });
    }
});

// http://stackoverflow.com/questions/654112/how-do-you-detect-support-for-vml-or-svg-in-a-browser
function supportsSvg() {
    return document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image", "1.1");
}
