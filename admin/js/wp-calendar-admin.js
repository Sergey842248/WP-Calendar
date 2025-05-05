(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize calendar
        if ($('#wp-calendar-admin').length) {
            initializeCalendar();
        }

        // Initialize datepickers
        $('.wp-calendar-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        // Initialize appointment form
        $('#wp-calendar-appointment-form').on('submit', function(e) {
            e.preventDefault();
            saveAppointment();
        });

        // Initialize blocked time form
        $('#wp-calendar-blocked-time-form').on('submit', function(e) {
            e.preventDefault();
            saveBlockedTime();
        });

        // Toggle recurring options
        $('#wp_calendar_is_recurring').on('change', function() {
            toggleRecurringOptions();
        });
        toggleRecurringOptions();

        // Delete appointment button
        $('.wp-calendar-delete-appointment').on('click', function(e) {
            e.preventDefault();
            if (confirm(wp_calendar_admin.i18n.confirm_delete)) {
                deleteAppointment($(this).data('id'));
            }
        });

        // Delete blocked time button
        $('.wp-calendar-delete-blocked-time').on('click', function(e) {
            e.preventDefault();
            if (confirm(wp_calendar_admin.i18n.confirm_delete_block)) {
                deleteBlockedTime($(this).data('id'));
            }
        });
    });

    function initializeCalendar() {
        $('#wp-calendar-admin').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: true,
            eventLimit: true,
            events: function(start, end, timezone, callback) {
                $.ajax({
                    url: wp_calendar_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wp_calendar_get_appointments',
                        nonce: wp_calendar_admin.nonce,
                        start: start.format('YYYY-MM-DD'),
                        end: end.format('YYYY-MM-DD')
                    },
                    success: function(response) {
                        if (response.success) {
                            callback(response.data);
                        } else {
                            alert(response.data || wp_calendar_admin.i18n.error);
                        }
                    },
                    error: function() {
                        alert(wp_calendar_admin.i18n.error);
                    }
                });
            },
            eventClick: function(calEvent, jsEvent, view) {
                // Handle event click
                if (calEvent.id.toString().startsWith('blocked_')) {
                    // Blocked time event
                    var blockedId = calEvent.id.toString().replace('blocked_', '');
                    window.location.href = 'admin.php?page=wp-calendar-blocked&action=edit&id=' + blockedId;
                } else {
                    // Regular appointment
                    window.location.href = 'admin.php?page=wp-calendar-appointments&action=edit&id=' + calEvent.id;
                }
            },
            dayClick: function(date, jsEvent, view) {
                // Open new appointment form for the clicked date
                window.location.href = 'admin.php?page=wp-calendar-appointments&action=add&date=' + date.format('YYYY-MM-DD');
            },
            eventDrop: function(event, delta, revertFunc) {
                // Handle event drag & drop
                if (event.id.toString().startsWith('blocked_')) {
                    // Can't move blocked times
                    revertFunc();
                    return;
                }

                // Update appointment date/time
                $.ajax({
                    url: wp_calendar_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wp_calendar_save_appointment',
                        nonce: wp_calendar_admin.nonce,
                        id: event.id,
                        user_id: event.user_id,
                        date: event.start.format('YYYY-MM-DD'),
                        time: event.start.format('HH:mm:ss'),
                        status: event.status,
                        notes: event.notes
                    },
                    success: function(response) {
                        if (!response.success) {
                            revertFunc();
                            alert(response.data || wp_calendar_admin.i18n.error);
                        }
                    },
                    error: function() {
                        revertFunc();
                        alert(wp_calendar_admin.i18n.error);
                    }
                });
            }
        });
    }

    function saveAppointment() {
        var form = $('#wp-calendar-appointment-form');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();

        submitBtn.prop('disabled', true).text(wp_calendar_admin.i18n.loading);

        $.ajax({
            url: wp_calendar_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_save_appointment',
                nonce: wp_calendar_admin.nonce,
                id: form.find('#appointment_id').val(),
                user_id: form.find('#user_id').val(),
                date: form.find('#appointment_date').val(),
                time: form.find('#appointment_time').val(),
                status: form.find('#appointment_status').val(),
                notes: form.find('#appointment_notes').val()
            },
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (response.success) {
                    $('#wp-calendar-message')
                        .removeClass('error')
                        .addClass('updated')
                        .html('<p>' + response.data.message + '</p>')
                        .show();
                    
                    // Redirect to appointments list after a short delay
                    setTimeout(function() {
                        window.location.href = 'admin.php?page=wp-calendar-appointments';
                    }, 1500);
                } else {
                    $('#wp-calendar-message')
                        .removeClass('updated')
                        .addClass('error')
                        .html('<p>' + (response.data || wp_calendar_admin.i18n.error) + '</p>')
                        .show();
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text(originalText);
                $('#wp-calendar-message')
                    .removeClass('updated')
                    .addClass('error')
                    .html('<p>' + wp_calendar_admin.i18n.error + '</p>')
                    .show();
            }
        });
    }

    function deleteAppointment(id) {
        $.ajax({
            url: wp_calendar_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_delete_appointment',
                nonce: wp_calendar_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = 'admin.php?page=wp-calendar-appointments&deleted=1';
                } else {
                    alert(response.data || wp_calendar_admin.i18n.error);
                }
            },
            error: function() {
                alert(wp_calendar_admin.i18n.error);
            }
        });
    }

    function saveBlockedTime() {
        var form = $('#wp-calendar-blocked-time-form');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();

        submitBtn.prop('disabled', true).text(wp_calendar_admin.i18n.loading);

        var isRecurring = form.find('#wp_calendar_is_recurring').is(':checked');
        
        $.ajax({
            url: wp_calendar_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_save_blocked_time',
                nonce: wp_calendar_admin.nonce,
                id: form.find('#blocked_id').val(),
                date: isRecurring ? '' : form.find('#blocked_date').val(),
                time: form.find('#blocked_time').val(),
                is_recurring: isRecurring ? 1 : 0,
                day_of_week: isRecurring ? form.find('#day_of_week').val() : ''
            },
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (response.success) {
                    $('#wp-calendar-message')
                        .removeClass('error')
                        .addClass('updated')
                        .html('<p>' + response.data.message + '</p>')
                        .show();
                    
                    // Redirect to blocked times list after a short delay
                    setTimeout(function() {
                        window.location.href = 'admin.php?page=wp-calendar-blocked';
                    }, 1500);
                } else {
                    $('#wp-calendar-message')
                        .removeClass('updated')
                        .addClass('error')
                        .html('<p>' + (response.data || wp_calendar_admin.i18n.error) + '</p>')
                        .show();
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text(originalText);
                $('#wp-calendar-message')
                    .removeClass('updated')
                    .addClass('error')
                    .html('<p>' + wp_calendar_admin.i18n.error + '</p>')
                    .show();
            }
        });
    }

    function deleteBlockedTime(id) {
        $.ajax({
            url: wp_calendar_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_delete_blocked_time',
                nonce: wp_calendar_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = 'admin.php?page=wp-calendar-blocked&deleted=1';
                } else {
                    alert(response.data || wp_calendar_admin.i18n.error);
                }
            },
            error: function() {
                alert(wp_calendar_admin.i18n.error);
            }
        });
    }

    function toggleRecurringOptions() {
        var isRecurring = $('#wp_calendar_is_recurring').is(':checked');
        
        if (isRecurring) {
            $('.wp-calendar-recurring-options').show();
            $('.wp-calendar-date-options').hide();
        } else {
            $('.wp-calendar-recurring-options').hide();
            $('.wp-calendar-date-options').show();
        }
    }

})(jQuery);