/**
 * JQuery plugin for creating swimlanes from server-side data
 *
 *
 * Uses this library: http://almende.github.io/chap-links-library/js/timeline/doc/#Configuration_Options
 * The parameters for the data format are the same as here.
 *
 * @param raw_data
 * @param options
 * @returns {$.fn}
 */

var gl_swimlanes = {};

var group_order_index = {"BC": 0, "ML": 10, "tdX": 20, "tdx": 20, "3pl": 30};

$.fn.tdxSwimlane = function(raw_data, options) {

    var defaults = {
        width: '100%',
        height: 'auto',
        minHeight: '300px',
        style: 'box',
        zoomMin: 1000 * 60 * 15 * 15, // 15 minutes, in milliseconds. There is a bug which means this is off by a factor of 15, testing is needed on live systems
        editable: false,
        status_property: false,
        show_900s: true,
        show_non_900s: true,
        guid: guid(),
        legend: "legend",
        customStackOrder: order_events,
        groupsOrder: order_groups,
        showMajorLabels: false,
        showMinorLabels: false
    };

    var settings = $.extend({}, defaults, options);
    this.data = tdxtimeline_parse_data(raw_data, settings);

    // Convert to Swimlane data
    this.data = tdxswimlane_convert_to_swimlane(this.data);

    console.log("Parsed timeline data:");
    console.log(this.data);
    this.html(this.data);
    var selector = this.selector.substring(1);

    // Set maximum and minimum dates
    // By default, these are one hour events
    var a_date_range = compute_date_range(this.data);
    a_date_range['min'].setHours(a_date_range['min'].getHours() - 24);
    a_date_range['max'].setHours(a_date_range['max'].getHours() + 24);
    if (!("min" in settings)) {
        // Only set minimum if not already set
        settings['min'] = a_date_range['min'];
    }
    if (!("max" in settings)) {
        // Only set maximum if not already set
        settings['max'] = a_date_range['max'];
    }

    this.timeline = new links.Timeline(document.getElementById(selector), settings);
    this.timeline.draw(this.data);

    // Optionally draw the legend, if a div id given
    if ("legend" in settings){
        draw_legend(this.data, settings['legend']);
    }

    gl_timelines[settings['guid']] = this;

    this.settings = settings;

    links.events.addListener(this.timeline, 'select', function(something){
        var index = gl_timelines[settings['guid']].timeline.selection.index;
        var d = gl_timelines[settings['guid']].data[index];
        //date n time from-to, sequence , status number
        /*{ so_order_no: "1810932.00000", so_bo_suffix: "None", so_line_seq: "None", order_log_date: "2015-04-02 00:00:00",
         order_log_time: "17:40:05", start: Date 2015-04-02T06:40:05.000Z, className: "30", title: "30",
         content: "#9: 30", end: Date 2015-04-07T06:20:29.000Z }
         */

        var event_detail = "";
        if (d["so_line_seq"] && d["so_line_seq"] != "None"){
            event_detail += "Seq: " + d["so_line_seq"] + ", ";
        }
        if (d["start"]){
            s_date = new Date(d["start"]);
            console.log(s_date);
            event_detail += "Time: " + s_date.toGMTString() + ", ";
        }
        if (d["end"]){
            s_date = new Date(d["end"]);
            event_detail += " to: " + s_date.toGMTString() + ", ";
        }
        if (d["order_status"]) {
            event_detail += "Status: " + d["order_status"] + " ";
        }
        $("#timeline_detail").html(event_detail);
    });


    return this;
};

function order_groups(item1, item2){
    if (item1 in group_order_index && item2 in group_order_index){
        return group_order_index[item1] < group_order_index[item2]
    }


    if (has(item1, 'content') && has(item2, 'content') && item1.content in group_order_index && item2.content in group_order_index){
        return group_order_index[item1.content] < group_order_index[item2.content]
    }

    return item1 < item2;
}


function tdxswimlane_convert_to_swimlane(timeline_data){
    /**
     * Swimlanes are variants of timelines, except that:
     * 1. NO LONGER DOING The enddate of a given event is the startdate of the next event
     * 2. TODO Events point to the next event with HTML added to the content
     */

    var swimlane_data = [];
    // Get all groups and order them in the same way they will be ordered on the timeline

    for (var i = 0; i<timeline_data.length; i++){
        var row = timeline_data[i];

        if (i < timeline_data.length - 1){
            var next_row = null;
            // Find the next row that has a false (or non-existant) value for "swimlane_exluded"
            for (j=1; j< timeline_data.length - i; j++){
                // Value must exist and be True to be excluded. i.e. true by default
                if (has(timeline_data[i+j], "swimlane_excluded") && (timeline_data[i+j]["swimlane_excluded"])){
                    continue; // Search next row
                }
                console.debug(row['content']);
                console.debug("i:" + i);
                console.debug("j:" + j);
                console.debug(has(timeline_data[i+j], "swimlane_excluded"));
                console.debug(timeline_data[i+j]["swimlane_excluded"]);
                next_row = timeline_data[i+j];
                break;
            }
            if (next_row) {
                // If the CURRENT row is to be excluded from the swimlane direction, don't give it a direction too
                if (!(has(timeline_data[i], "swimlane_excluded") && (timeline_data[i ]["swimlane_excluded"]))) {
                    var next_event_spot = "right";
                    if (row['group'] != next_row['group']) {
                        // Events are not on the same group, is the next one up or down?
                        // We use the same ordering function as the timeline for this
                        var next_event_down = order_groups(row['group'], next_row['group']);

                        if (next_event_down) {
                            next_event_spot = "down";
                        } else {
                            next_event_spot = "up";
                        }
                    }

                    row['content'] += ' <i class="fa fa-arrow-' + next_event_spot + '" aria-hidden="true"></i>';
                }
            }
        }

        swimlane_data.push(row);
    }

    return swimlane_data;
}

function has(object, key) {
    return object ? hasOwnProperty.call(object, key) : false;
}
