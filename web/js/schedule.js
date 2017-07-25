$(document).ready(function() {
    $('#calendar').fullCalendar({ // применяем fullCalendar к элементу #calendar
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        //titleFormat: '[Группа 1.1]',
        firstDay: 1,
        locale: 'ru',
        selectHelper: true,
        dayClick: function(date, jsEvent, view) {
            transform_date(date);
            showModal();
        },
        events:defaults.events_load_url,
        timeFormat: 'H:mm',
        eventRender: function(event, element) {
            if(event.description) {
                element.qtip({
                    content: event.description,
                    position: {target: 'mouse', adjust: {mouse: false}},
                });
            }
        },
        minTime: "07:00:00",
        maxTime: "23:00:00",
        allDaySlot: false,
        displayEventEnd: true,
        eventClick: function(event, element) {
            var start = transform_time(event.start);
            var end = transform_time(event.end);
            $('#schedule_start_time_hour').val(start.time_hour);
            $('#schedule_start_time_minute').val(start.time_minute);
            $('#schedule_end_time_hour').val(end.time_hour);
            $('#schedule_end_time_minute').val(end.time_minute);
            $('#schedule_description').val(event.description);
            $('#schedule_teacher_lesson_id').val(event.title_id);
            $('#schedule_dow').val(event.dow);
            transform_date(event.start);
        }


    });
    function transform_date(date)
    {
        var current_day = date.format('D');
        var current_month = date.format('M');
        var current_year = date.format('YYYY');
        $('#schedule_start_date_year').val(current_year);
        $('#schedule_start_date_month').val(current_month);
        $('#schedule_start_date_day').val(current_day);
    }
    function transform_time(time)
    {
        var time_hour = time.format('H');
        var time_minute = time.format('m');
        return {
            'time_hour': time_hour,
            'time_minute': time_minute
        };
    }
    $('#calendar').fullCalendar('refetchEvents');
});
