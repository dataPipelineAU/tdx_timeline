/**
 * JQuery plugin for creating timelines from server-side data
 *
 *
 * Uses this library: http://almende.github.io/chap-links-library/js/timeline/doc/#Configuration_Options
 * The parameters for the data format are the same as here.
 *
 * @param raw_data
 * @param options
 * @returns {$.fn}
 */

var gl_timelines = {};

$.fn.tdxTimeline = function(raw_data, options) {

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
        legend: "legend"
    };

    var settings = $.extend({}, defaults, options);
    this.data = tdxtimeline_parse_data(raw_data, settings);
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
            event_detail += "Time: " + s_date.format("dd/m/yy HH:MM") + ", ";
        }
        if (d["end"]){
            s_date = new Date(d["end"]);
            event_detail += " to: " + s_date.format("dd/m/yy HH:MM") + ", ";
        }
        if (d["order_status"]) {
            event_detail += "Status: " + d["order_status"] + " ";
        }
        $("#timeline_detail").html(event_detail);
    });


    return this;
};


function guid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
        s4() + '-' + s4() + s4() + s4();
}


if (typeof String.prototype.startsWith != 'function') {
    String.prototype.startsWith = function (str){
        return this.slice(0, str.length) == str;
    };
}

if(!String.prototype.trim) {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g,'');
    };
}


function nuller(){
    // Filter out (or don't) events based on status code
    // TODO: Move this to event parsing
    // TODO: Option to decide whether to do this, or the min/max computation first
    var status_property = settings["status_property"];
    if (status_property) {
        var new_data = [];
        for (var key in this.data) {
            var event = this.data[key];
            var property_value = parseInt(event[status_property]);
            console.log("Event");
            console.log(event);
            if (property_value >= 900 && settings["show_900s"]){
                console.log("Added (more than 900, and those are set to show)");
                new_data.push(event);
            }else if (property_value < 900 && settings["show_non_900s"]){
                new_data.push(event);
                console.log("Added (less than 900, and those are set to show)");
            }else{
                console.log("Not added: " + property_value);
                console.log(" - Original: " + event[status_property]);
            }
        }
        this.data = new_data;
    }
}


function tdxtimeline_parse_data(raw_data, settings){
    // Parses the raw data into events
    var events = [];

    var current_event = null;

    $.each(raw_data.split("\n"), function(index, line){
        trimmed_line = line.trim();
        if (trimmed_line.length == 0 || trimmed_line.startsWith("#")){
            // Ignore line, is either blank or a comment
        }else if (line.startsWith(" ") || line.startswith("\t")) {
            // This line is a property of the existing event
            // Everything to the left of the first colon (:) is the key
            // Everything to the right is the value
            // Both are stripped of leading and trailing whitespace
            var s = trimmed_line.split(/:(.+)?/);
            var key = s[0].trim();
            var value = "None";
            if (s.length > 1 && s[1]) {
                value = s[1].trim();
            }
            if (key == "description" && !current_event.content){
                current_event["content"] = value;
            }else if (key == "start" || key == "end"){
                // Parse date
                value = new Date(value);
            }

            current_event[key] = value;

        }else{
            // New event. We don't care about the name.
            if (current_event){
                events.push(current_event);
            }
            current_event = {};
        }
    });
    if (current_event && current_event != {}) {
        events.push(current_event);
    }
    return events;

}


function compute_date_range(data){
    var a_daterange = {};
    a_daterange['min'] = null;
    a_daterange['max'] = null;
    for (var eventname in data) {
        if (!data.hasOwnProperty(eventname)){
            // ignore prototype properties, as per http://stackoverflow.com/questions/558981/iterating-through-list-of-keys-for-associative-array-in-json
            continue;
        }
        var cur_event = data[eventname];
        if (a_daterange['min'] == null || cur_event['start'] < a_daterange['min']){
            a_daterange['min'] = new Date(cur_event['start'].getTime());
        }
        if (cur_event['end'] && (a_daterange['max'] == null || cur_event['end'] > a_daterange['max'])){
            a_daterange['max'] = new Date(cur_event['end'].getTime());
        }
    }
    return a_daterange;
}


function draw_legend(event_data, div_identifier){
    // Extract different classType from the event data
    var event_class_types = {};
    for (var i=0; i< event_data.length; i++){
        if ('classType' in event_data[i]){
            event_class_types[event_data[i]['classType']] = event_data[i]['className'];
        }
    }
    // Build code for legend
    var s_code = "<h2 class='legend_header'>Legend</h2>";
    for (var classType in event_class_types) {
        s_code += "<div class='legenditem " + event_class_types[classType] + "'>";
        s_code += classType;
        s_code += "</div>";
    }
    $(div_identifier).html(s_code);
}

