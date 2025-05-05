(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize calendar
        if ($('#wp-calendar-public').length) {
            initializeCalendar();
        }

        // Initialize datepicker and booking form logic
        var bookingForm = $('#wp-calendar-booking-form');

        if (bookingForm.length) {
            // Initialize datepicker
            $('.wp-calendar-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                beforeShowDay: function(date) {
                    // Add logic to disable blocked dates if needed
                    return [true, ''];
                },
                onSelect: function(dateText) {
                    // When a date is selected, get available times
                    getAvailableTimes(dateText);
                }
            });

            // Handle form submission
            bookingForm.on('submit', function(e) {
                e.preventDefault();

                var dateField = $('#appointment_date');
                var timeField = $('#appointment_time');
                var notesField = $('#appointment_notes');

                // Validate fields
                if (!dateField.val()) {
                    showMessage('error', wp_calendar_public.i18n.select_date);
                    return false;
                }

                if (!timeField.val()) {
                    showMessage('error', wp_calendar_public.i18n.select_time);
                    return false;
                }

                // Show loading message
                showMessage('info', wp_calendar_public.i18n.loading);

                // Submit the booking
                $.ajax({
                    url: wp_calendar_public.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wp_calendar_book_appointment',
                        nonce: wp_calendar_public.nonce,
                        date: dateField.val(),
                        time: timeField.val(),
                        notes: notesField.val()
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('success', response.data.message);
                            // Reset form
                            bookingForm[0].reset();
                            timeField.empty().append('<option value="">' + wp_calendar_public.i18n.select_date + '</option>').prop('disabled', true);

                            // Reload calendar if it exists
                            if ($('#wp-calendar-public').length) {
                                $('#wp-calendar-public').fullCalendar('refetchEvents');
                            }
                        } else {
                            showMessage('error', response.data);
                        }
                    },
                    error: function() {
                        showMessage('error', wp_calendar_public.i18n.booking_error);
                    }
                });

                return false;
            });
        }

        // Login form submission
        $('#wp-calendar-login-form').on('submit', function(e) {
            e.preventDefault();
            loginUser();
        });

        // Register form submission
        $('#wp-calendar-register-form').on('submit', function(e) {
            e.preventDefault();
            registerUser();
        });

        // Cancel appointment button
        $(document).on('click', '.wp-calendar-cancel-appointment', function(e) {
            e.preventDefault();
            var appointmentId = $(this).data('id');
            if (confirm(wp_calendar_public.i18n.confirm_cancel)) {
                cancelAppointment(appointmentId);
            }
        });
    });

    function initializeCalendar() {
        $('#wp-calendar-public').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultView: 'month',
            editable: false,
            eventLimit: true,
            events: function(start, end, timezone, callback) {
                $.ajax({
                    url: wp_calendar_public.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wp_calendar_get_public_events',
                        nonce: wp_calendar_public.nonce,
                        start: start.format('YYYY-MM-DD'),
                        end: end.format('YYYY-MM-DD')
                    },
                    success: function(response) {
                        if (response.success) {
                            callback(response.data);
                        } else {
                            alert(response.data || wp_calendar_public.i18n.booking_error);
                        }
                    },
                    error: function() {
                        alert(wp_calendar_public.i18n.booking_error);
                    }
                });
            },
            dayClick: function(date, jsEvent, view) {
                // Check if user is logged in
                if (!wp_calendar_public.is_logged_in) {
                    alert(wp_calendar_public.i18n.login_required);
                    window.location.href = wp_calendar_public.login_url;
                    return;
                }

                // Set the selected date in the booking form
                $('#appointment_date').val(date.format('YYYY-MM-DD')).trigger('change');

                // Scroll to booking form
                $('html, body').animate({
                    scrollTop: $('#wp-calendar-booking-form').offset().top - 50
                }, 500);
            },
            eventClick: function(calEvent, jsEvent, view) {
                // Only handle user's own appointments
                if (calEvent.className && calEvent.className.indexOf('wp-calendar-user-appointment') !== -1) {
                    // Show appointment details
                    var appointmentId = calEvent.id.replace('appointment_', '');
                    showAppointmentDetails(appointmentId);
                }
            }
        });
    }

    function cancelAppointment(appointmentId) {
        $.ajax({
            url: wp_calendar_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_cancel_appointment',
                nonce: wp_calendar_public.nonce,
                id: appointmentId
            },
            success: function(response) {
                if (response.success) {
                    alert(wp_calendar_public.i18n.cancel_success);
                    location.reload();
                } else {
                    alert(response.data || wp_calendar_public.i18n.cancel_error);
                }
            },
            error: function() {
                alert(wp_calendar_public.i18n.cancel_error);
            }
        });
    }

    function showAppointmentDetails(appointmentId) {
        // This function would show a modal with appointment details
        // For simplicity, we'll just redirect to the account page
        window.location.href = wp_calendar_public.account_url;
    }

    function loginUser() {
        var form = $('#wp-calendar-login-form');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();

        submitBtn.prop('disabled', true).text(wp_calendar_public.i18n.loading);

        $.ajax({
            url: wp_calendar_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_login',
                nonce: wp_calendar_public.nonce,
                username: form.find('#username').val(),
                password: form.find('#password').val(),
                remember: form.find('#remember').is(':checked') ? 1 : 0
            },
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);

                if (response.success) {
                    form.find('.wp-calendar-message')
                        .removeClass('error')
                        .addClass('success')
                        .text(response.data.message)
                        .show();

                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    form.find('.wp-calendar-message')
                        .removeClass('success')
                        .addClass('error')
                        .text(response.data)
                        .show();
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text(originalText);
                form.find('.wp-calendar-message')
                    .removeClass('success')
                    .addClass('error')
                    .text(wp_calendar_public.i18n.booking_error)
                    .show();
            }
        });
    }

    function registerUser() {
        var form = $('#wp-calendar-register-form');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();

        // Validate passwords match
        var password = form.find('#password').val();
        var passwordConfirm = form.find('#password_confirm').val();

        if (password !== passwordConfirm) {
            form.find('.wp-calendar-message')
                .removeClass('success')
                .addClass('error')
                .text(wp_calendar_public.i18n.passwords_not_match)
                .show();
            return;
        }

        submitBtn.prop('disabled', true).text(wp_calendar_public.i18n.loading);

        $.ajax({
            url: wp_calendar_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_register',
                nonce: wp_calendar_public.nonce,
                username: form.find('#username').val(),
                email: form.find('#email').val(),
                password: password,
                password_confirm: passwordConfirm
            },
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);

                if (response.success) {
                    form.find('.wp-calendar-message')
                        .removeClass('error')
                        .addClass('success')
                        .text(response.data.message)
                        .show();

                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    form.find('.wp-calendar-message')
                        .removeClass('success')
                        .addClass('error')
                        .text(response.data)
                        .show();
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text(originalText);
                form.find('.wp-calendar-message')
                    .removeClass('success')
                    .addClass('error')
                    .text(wp_calendar_public.i18n.booking_error)
                    .show();
            }
        });
    }

    // Function to get available times for a selected date
    // Suchen Sie nach der Funktion, die die verfügbaren Zeiten abruft
    function getAvailableTimes(date) {
        var timeSelect = $('#appointment_time');
        
        // Zeige Ladetext
        timeSelect.empty().append('<option value="">' + wp_calendar_public.i18n.loading + '</option>').prop('disabled', true);
        
        // Fügen Sie Debugging-Informationen hinzu
        console.log('Requesting available times for date:', date);
        console.log('AJAX URL:', wp_calendar_public.ajax_url);
        console.log('Nonce:', wp_calendar_public.nonce);
        
        $.ajax({
            url: wp_calendar_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_get_available_times',
                nonce: wp_calendar_public.nonce,
                date: date
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                
                timeSelect.empty();
                
                if (response.success && response.data && response.data.length > 0) {
                    // Füge die verfügbaren Zeiten hinzu
                    $.each(response.data, function(index, time) {
                        timeSelect.append('<option value="' + time.value + '">' + time.label + '</option>');
                    });
                    timeSelect.prop('disabled', false);
                } else {
                    // Wenn keine Zeiten verfügbar sind, zeige eine entsprechende Nachricht
                    timeSelect.append('<option value="">' + wp_calendar_public.i18n.no_times_available + '</option>');
                    timeSelect.prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                timeSelect.empty().append('<option value="">' + wp_calendar_public.i18n.booking_error + '</option>');
                timeSelect.prop('disabled', true);
            }
        });
    }

    // Function to show messages
    function showMessage(type, message) {
        var messageDiv = $('.wp-calendar-message');
        messageDiv.removeClass('error success info').addClass(type).html(message).show();

        // Scroll to message
        $('html, body').animate({
            scrollTop: messageDiv.offset().top - 100
        }, 500);
    }

})(jQuery);
