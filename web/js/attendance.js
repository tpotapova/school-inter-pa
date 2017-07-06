$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'agendaWeek,agendaDay'
        },
        firstDay: 1,
        locale: 'ru',
        events: defaults.events_load_url,
        editable: true,
        timeFormat: 'H:mm',
        eventRender: function(event, element) {
            element[0].removeAttribute('href');
        },
        /*eventClick: function(calEvent, jsEvent, view) {
            showModal();
                    $("input[id$='date']").val(calEvent.start.format('YYYY-MM-DD'));
                    $('#date_caption').text(calEvent.start.format('DD-MM-YYYY'));
                    //$("input[id$='teacher_lesson']").val(parseInt(calEvent.title_id));
                    //$("input[id$='teacher_lesson']").val(calEvent.teacher_lesson);
                    $('#teacher_lesson_caption').text(calEvent.title);
                    $("input[id$='start_time']").val(calEvent.start.format('hh:mm:ss'));
                    $('#start_time_caption').text(calEvent.start.format('hh:mm'));
                    $('[id$=teacher_lesson]').val(calEvent.title_id);
                    $('[id$=save]').click(function() {
                        calEvent.color ='green';

                $('#calendar').fullCalendar('updateEvent', calEvent);
            });
        },*/
        eventClick: function(event) {
            if (event.url) {
                window.open(event.url,'_parent');
                return false;
            }
        },
        minTime: "07:00:00",
        maxTime: "23:00:00",
        allDaySlot: false,
        displayEventEnd: true,
    });

    $('#calendar').fullCalendar('rerenderEvents');

});