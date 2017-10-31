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
        timeFormat: 'H:mm',
        eventRender: function(event, element) {
            element[0].removeAttribute('href');
            var title = element.find( '.fc-title' );
            title.html( title.text() );
        },

        eventClick: function(event) {
            $('#teacher_lesson_caption').text(event.title);
            if (event.url) {
                window.open(event.url,'_parent');
                return false;
            }
        },
        minTime: "07:00:00",
        maxTime: "23:00:00",
        allDaySlot: false,
        displayEventEnd: true,
        viewRender:(function() {
            return function(view, element) {
            }
        }),
    });

});
