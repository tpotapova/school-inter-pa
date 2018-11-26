$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'agendaWeek,agendaDay'
        },
        defaultView: 'agendaWeek',
        firstDay: 1,
        locale: 'ru',
        events: events_load_url,
        timeFormat: 'H:mm',
        eventRender: function(event, element) {
            element[0].removeAttribute('href');
            element.find('span.fc-event-title').html(element.find('span.fc-event-title').text());
        },
        eventClick:  function(event, jsEvent, view) {
            $('#modalTitle').html('Домашнее задание' +"\n" + event.title);
            $('#modalBody').html(event.homework);
            $('#calendarModal').modal();
        },
        minTime: "07:00:00",
        maxTime: "23:00:00",
        allDaySlot: false,
        displayEventEnd: true,
    });
    $('#calendar').fullCalendar('rerenderEvents');
});
