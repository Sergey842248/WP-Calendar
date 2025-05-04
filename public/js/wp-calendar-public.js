(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize calendar
        if ($('#wp-calendar-public').length) {
            initializeCalendar();
        }

        // Initialize datepicker - FIXED VERSION
        if ($('.wp-calendar-datepicker').length) {
            $('.wp-calendar-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                changeMonth: true,
                changeYear: true,
                beforeShowDay: function(date) {
                    // Add logic to disable blocked dates if needed
                    return [true, ''];
                },
                onSelect: function(dateText) {
                    // When a date is selected, get available times
                    getAvailableTimes(dateText);
                }
            });
            
            console.log('Datepicker initialized');
        }

        // Book appointment form submission
        $('#wp-calendar-booking-form').on('submit', function(e) {
            e.preventDefault();
            bookAppointment();
        });

        // Cancel appointment button
        $(document).on('click', '.wp-calendar-cancel-appointment', function(e) {
            e.preventDefault();
            var appointmentId = $(this).data('id');
            if (confirm(wp_calendar_public.i18n.confirm_cancel)) {
                cancelAppointment(appointmentId);
            }
        });

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

        // Date selection in booking form
        $('#appointment_date').on('change', function() {
            var selectedDate = $(this).val();
            if (selectedDate) {
                getAvailableTimes(selectedDate);
            } else {
                $('#appointment_time').html('<option value="">' + wp_calendar_public.i18n.select_time + '</option>');
                $('#appointment_time').prop('disabled', true);
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

    function getAvailableTimes(date) {
        $('#appointment_time').html('<option value="">' + wp_calendar_public.i18n.loading + '</option>');
        $('#appointment_time').prop('disabled', true);

        $.ajax({
            url: wp_calendar_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_get_available_times',
                nonce: wp_calendar_public.nonce,
                date: date
            },
            success: function(response) {
                if (response.success) {
                    var times = response.data;
                    var options = '<option value="">' + wp_calendar_public.i18n.select_time + '</option>';

                    if (times.length === 0) {
                        $('#appointment_time').html('<option value="">' + wp_calendar_public.i18n.no_times_available + '</option>');
                        $('#appointment_time').prop('disabled', true);
                    } else {
                        for (var i = 0; i < times.length; i++) {
                            options += '<option value="' + times[i].value + '">' + times[i].label + '</option>';
                        }
                        $('#appointment_time').html(options);
                        $('#appointment_time').prop('disabled', false);
                    }
                } else {
                    $('#appointment_time').html('<option value="">' + wp_calendar_public.i18n.select_time + '</option>');
                    $('#appointment_time').prop('disabled', true);
                    alert(response.data || wp_calendar_public.i18n.booking_error);
                }
            },
            error: function() {
                $('#appointment_time').html('<option value="">' + wp_calendar_public.i18n.select_time + '</option>');
                $('#appointment_time').prop('disabled', true);
                alert(wp_calendar_public.i18n.booking_error);
            }
        });
    }

    function bookAppointment() {
        var form = $('#wp-calendar-booking-form');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();

        // Validate form
        var date = form.find('#appointment_date').val();
        var time = form.find('#appointment_time').val();

        if (!date) {
            alert(wp_calendar_public.i18n.select_date);
            return;
        }

        if (!time) {
            alert(wp_calendar_public.i18n.select_time);
            return;
        }

        submitBtn.prop('disabled', true).text(wp_calendar_public.i18n.loading);

        $.ajax({
            url: wp_calendar_public.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_calendar_book_appointment',
                nonce: wp_calendar_public.nonce,
                date: date,
                time: time,
                notes: form.find('#appointment_notes').val()
            },
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (response.success) {
                    // Show success message
                    form.find('.wp-calendar-message')
                        .removeClass('error')
                        .addClass('success')
                        .text(wp_calendar_public.i18n.booking_success)
                        .show();
                    
                    // Reset form
                    form[0].reset();
                    $('#appointment_time').html('<option value="">' + wp_calendar_public.i18n.select_time + '</option>');
                    $('#appointment_time').prop('disabled', true);
                    
                    // Refresh calendar
                    $('#wp-calendar-public').fullCalendar('refetchEvents');
                    
                    // Redirect to account page after a delay
                    setTimeout(function() {
                        window.location.href = wp_calendar_public.account_url;
                    }, 2000);
                } else {
                    form.find('.wp-calendar-message')
                        .removeClass('success')
                        .addClass('error')
                        .text(response.data || wp_calendar_public.i18n.booking_error)
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

    // Check if the booking form exists and add event listener
    jQuery(document).ready(function($) {
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
        
        // Function to get available times for a selected date
        function getAvailableTimes(date) {
            var timeField = $('#appointment_time');
            
            // Reset and disable time field
            timeField.empty().append('<option value="">' + wp_calendar_public.i18n.loading + '</option>').prop('disabled', true);
            
            $.ajax({
                url: wp_calendar_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_calendar_get_available_times',
                    nonce: wp_calendar_public.nonce,
                    date: date
                },
                success: function(response) {
                    timeField.empty();
                    
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(i, slot) {
                            timeField.append('<option value="' + slot.value + '">' + slot.label + '</option>');
                        });
                        timeField.prop('disabled', false);
                    } else {
                        timeField.append('<option value="">' + wp_calendar_public.i18n.no_times_available + '</option>');
                    }
                },
                error: function() {
                    timeField.empty().append('<option value="">' + wp_calendar_public.i18n.booking_error + '</option>');
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
    });