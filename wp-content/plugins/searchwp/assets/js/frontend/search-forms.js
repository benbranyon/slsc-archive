( function($) {

    'use strict';

    const i18n = window.searchwpForms?.i18n || {};
    const __   = ( key, fallback ) => i18n[ key ] || fallback;

    /**
     * Voice search state manager. Tracks state per form and syncs UI.
     *
     * @since 4.5.8
     */
    const VoiceSearchState = {
        forms: {},

        /**
         * Get default form state object.
         *
         * @since 4.5.8
         *
         * @return {Object} Default form state.
         */
        getDefaultFormState() {
            return {
                state: 'idle',
                recognition: null,
                timeoutId: null,
                error: null,
                lastTranscript: '',
                speechStarted: false,
                intentionalStop: false,
                tooltipClickHandler: null,
                tooltipTimerId: null
            };
        },

        /**
         * Ensure form entry exists, creating it if needed.
         *
         * @since 4.5.8
         *
         * @param {string} formId Form ID.
         *
         * @return {Object} Form entry.
         */
        ensureFormEntry( formId ) {
            if ( ! this.forms[ formId ] ) {
                this.forms[ formId ] = this.getDefaultFormState();
            }
            return this.forms[ formId ];
        },

        getState( formId ) {
            return this.forms[ formId ] ? this.forms[ formId ].state : 'idle';
        },

        /**
         * Get form ID of the form currently in listening or processing state.
         *
         * @since 4.5.8
         *
         * @return {string|null} Active form ID, or null if none.
         */
        getActiveFormId() {
            for ( const id in this.forms ) {
                if ( this.forms[ id ].state === 'listening' || this.forms[ id ].state === 'processing' ) {
                    return id;
                }
            }
            return null;
        },

        hasSpeechStarted( formId ) {
            return this.forms[ formId ] ? this.forms[ formId ].speechStarted : false;
        },

        setSpeechStarted( formId, started ) {
            this.ensureFormEntry( formId ).speechStarted = started;
        },

        setState( formId, state, data = {} ) {
            const entry = this.ensureFormEntry( formId );
            entry.state = state;
            if ( data.recognition !== undefined ) {
                entry.recognition = data.recognition;
            }
            if ( data.error !== undefined ) {
                entry.error = data.error;
            }
            if ( data.lastTranscript !== undefined ) {
                entry.lastTranscript = data.lastTranscript;
            }
            this.updateButtonUI( formId );
        },

        getRecognition( formId ) {
            return this.forms[ formId ] ? this.forms[ formId ].recognition : null;
        },

        setRecognition( formId, recognition ) {
            this.ensureFormEntry( formId ).recognition = recognition;
        },

        clearTimeout( formId ) {
            const entry = this.forms[ formId ];
            if ( entry && entry.timeoutId ) {
                clearTimeout( entry.timeoutId );
                entry.timeoutId = null;
            }
        },

        /**
         * Stop recognition and optionally suppress "aborted" tooltip.
         *
         * @since 4.5.8
         *
         * @param {string} formId      Form ID.
         * @param {boolean} skipTooltip If true, onerror(aborted) will not show tooltip.
         */
        stopRecognition( formId, skipTooltip = true ) {
            const entry = this.forms[ formId ];
            if ( ! entry ) {
                return;
            }

            entry.intentionalStop = skipTooltip;

            if ( ! entry.recognition ) {
                return;
            }

            try {
                const stopMethod = entry.recognition.abort || entry.recognition.stop;
                if ( typeof stopMethod === 'function' ) {
                    stopMethod.call( entry.recognition );
                }
            } catch ( err ) {
                // Ignore if already ended.
            }
        },

        /**
         * Reset form state to idle. Consolidates common cleanup pattern.
         *
         * @since 4.5.8
         *
         * @param {string} formId Form ID.
         */
        resetToIdle( formId ) {
            this.stopRecognition( formId, true );
            this.setSpeechStarted( formId, false );
            this.setState( formId, 'idle', { error: null } );
        },

        setTimeout( formId, timeoutId ) {
            this.ensureFormEntry( formId ).timeoutId = timeoutId;
        },

        /**
         * Reset timeout when speech is detected. Clears existing timeout and starts a new one.
         *
         * @since 4.5.8
         *
         * @param {string} formId   Form ID.
         * @param {number} timeoutMs Timeout duration in milliseconds.
         */
        resetTimeout( formId, timeoutMs ) {
            this.clearTimeout( formId );
            const timeoutId = setTimeout( () => {
                this.stopRecognition( formId, true );
                this.clearTimeout( formId );
            }, timeoutMs );
            this.setTimeout( formId, timeoutId );
        },

        cleanup( formId ) {
            this.clearTimeout( formId );
            this.hideTooltip( formId );
            this.stopRecognition( formId, true );
            const entry = this.forms[ formId ];
            if ( entry ) {
                entry.recognition = null;
            }
            this.setState( formId, 'idle', { error: null } );
        },

        updateButtonUI( formId ) {
            const $button = $( '#' + formId ).find( '.swp-voice-search-button' );
            if ( ! $button.length ) {
                return;
            }
            const state = this.getState( formId );
            $button
                .removeClass( 'swp-voice-listening swp-voice-processing' )
                .attr( 'aria-label', $button.data( 'aria-label-idle' ) || __( 'voiceSearch', 'Voice Search' ) );
            if ( state === 'listening' ) {
                $button.addClass( 'swp-voice-listening' ).attr( 'aria-label', __( 'listening', 'Listening…' ) );
            } else if ( state === 'processing' ) {
                $button.addClass( 'swp-voice-processing' ).attr( 'aria-label', __( 'processing', 'Processing…' ) );
            }
        },

        /**
         * Get user-friendly message for error types.
         *
         * @since 4.5.8
         *
         * @param {string} errorType Error type from SpeechRecognition API.
         *
         * @return {string} User-friendly error message.
         */
        getTooltipMessage( errorType ) {
            const messages = {
                'no-speech': __( 'noSpeech', 'No speech detected. Please try speaking again.' ),
                'audio-capture': __( 'audioCapture', 'Microphone not found or unavailable.' ),
                'not-allowed': __( 'notAllowed', 'Microphone permission denied. Please allow access.' ),
                'network': __( 'network', 'Network error. Please check your connection.' ),
                'aborted': __( 'aborted', 'Voice search cancelled.' ),
                'service-not-allowed': __( 'serviceNotAllowed', 'Speech recognition service unavailable.' ),
                'empty-transcript': __( 'noSpeech', 'No speech detected. Please try speaking again.' )
            };
            return messages[ errorType ] || __( 'defaultError', 'Voice search error. Please try again.' );
        },

        /**
         * Create tooltip element if it doesn't exist.
         *
         * @since 4.5.8
         *
         * @param {string} formId Form ID.
         *
         * @return {jQuery} Tooltip element.
         */
        createTooltipElement( formId ) {
            const $form  = $( '#' + formId );
            let $tooltip = $form.find( '.swp-voice-search-tooltip' );

            if ( ! $tooltip.length ) {
                $tooltip = $( '<div class="swp-voice-search-tooltip" role="alert" aria-live="polite"></div>' );
                $form.find( '.searchwp-form-input-container' ).append( $tooltip );

                // Click to dismiss.
                $tooltip.on( 'click', () => {
                    this.hideTooltip( formId );
                } );
            }

            return $tooltip;
        },

        /**
         * Show tooltip with message.
         *
         * @since 4.5.8
         *
         * @param {string} formId  Form ID.
         * @param {string} message Tooltip message.
         * @param {string} type    Tooltip type (error, warning, info).
         */
        showTooltip( formId, message, type = 'error' ) {
            const $tooltip = this.createTooltipElement( formId );
            const $form    = $( '#' + formId );
            const entry    = this.forms[ formId ];

            // Clear any existing dismiss timer.
            if ( entry && entry.tooltipTimerId ) {
                clearTimeout( entry.tooltipTimerId );
                entry.tooltipTimerId = null;
            }

            // Determine tooltip position based on available viewport space.
            const $inputContainer = $form.find( '.searchwp-form-input-container' );
            if ( $inputContainer.length ) {
                const inputContainerRect = $inputContainer[ 0 ].getBoundingClientRect();
                const tooltipHeight      = 50; // Approximate tooltip height including arrow.
                const shouldShowBelow    = inputContainerRect.top < tooltipHeight + 16;
                $tooltip.toggleClass( 'swp-voice-search-tooltip--below', shouldShowBelow );
            }

            // Update tooltip content and styling.
            $tooltip
                .text( message )
                .removeClass( 'swp-voice-search-tooltip--error swp-voice-search-tooltip--warning swp-voice-search-tooltip--info' )
                .addClass( 'swp-voice-search-tooltip--' + type )
                .addClass( 'swp-voice-search-tooltip--visible' );

            // Update aria-describedby on button.
            const $button   = $( '#' + formId ).find( '.swp-voice-search-button' );
            const tooltipId = 'swp-voice-tooltip-' + formId;
            $tooltip.attr( 'id', tooltipId );
            $button.attr( 'aria-describedby', tooltipId );

            // Auto-dismiss after 4 seconds.
            if ( entry ) {
                entry.tooltipTimerId = setTimeout( () => {
                    this.hideTooltip( formId );
                }, 4000 );

                // Add document click handler to dismiss on click outside.
                if ( ! entry.tooltipClickHandler ) {
                    entry.tooltipClickHandler = ( event ) => {

                        const $target = $( event.target );
                        // Dismiss if click is outside the tooltip.
                        if ( ! $target.closest( '.swp-voice-search-tooltip' ).length ) {
                            this.hideTooltip( formId );
                        }
                    };
                    // Use setTimeout to avoid immediate dismissal from the current click event.
                    setTimeout( () => {
                        $( document ).on( 'click.swpVoiceTooltip-' + formId, entry.tooltipClickHandler );
                    }, 0 );
                }
            }
        },

        /**
         * Hide tooltip for a form.
         *
         * @since 4.5.8
         *
         * @param {string} formId Form ID.
         */
        hideTooltip( formId ) {
            const $form    = $( '#' + formId );
            const $tooltip = $form.find( '.swp-voice-search-tooltip' );
            const entry    = this.forms[ formId ];

            if ( entry && entry.tooltipTimerId ) {
                clearTimeout( entry.tooltipTimerId );
                entry.tooltipTimerId = null;
            }

            // Remove document click handler.
            if ( entry && entry.tooltipClickHandler ) {
                $( document ).off( 'click.swpVoiceTooltip-' + formId, entry.tooltipClickHandler );
                entry.tooltipClickHandler = null;
            }

            if ( $tooltip.length ) {
                $tooltip.removeClass( 'swp-voice-search-tooltip--visible' );
            }

            // Remove aria-describedby from button.
            const $button = $form.find( '.swp-voice-search-button' );
            $button.removeAttr( 'aria-describedby' );
        }
    };

    const app = {

        /**
         * Init.
         *
         * @since 4.3.2
         */
        init: () => {

            $( app.ready );
        },

        /**
         * Document ready
         *
         * @since 4.3.2
         */
        ready: () => {

            app.events();
        },

        /**
         * Plugin events.
         *
         * @since 4.3.2
         */
        events: () => {

            $( '.swp-toggle-checkbox' ).removeAttr( 'disabled' );

            $( '.swp-toggle-checkbox' ).on( 'change', function() {
                const $form         = $( this ).closest( 'form' );
                const $filters      = $form.find( '.searchwp-form-advanced-filters' );
                const $selects      = $filters.find( 'select' );
                const $toggleSwitch = $form.find( '.swp-toggle-switch' );
                const isChecked     = $( this ).is( ':checked' );

                // Update aria-checked attribute on the swp-toggle-switch to match the checked state.
                $toggleSwitch.attr( 'aria-checked', isChecked );

                if ( isChecked ) {
                    $filters.css( 'display', 'flex' );
                    $selects.prop( 'disabled', false );
                } else {
                    $filters.hide();
                    $selects.prop( 'disabled', true );
                }
            } );

            app.initVoiceSearch();
        },

        /**
         * Initialize voice search functionality.
         *
         * @since 4.5.8
         */
        initVoiceSearch: () => {

            const $buttons = $( '.swp-voice-search-button' );
            if ( ! $buttons.length ) {
                return;
            }

            if ( ! ( 'webkitSpeechRecognition' in window ) && ! ( 'SpeechRecognition' in window ) ) {
                return;
            }

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

			// Opera exposes SpeechRecognition but the recognition service does not work
			// (no mic prompt, no recognition). Hide the button until Opera fixes support.
			const isOpera = !!( window.opr && window.opr.addons ) ||
				navigator.userAgent.indexOf( 'OPR' ) > -1 ||
				navigator.vendor === 'Opera Software ASA';

			if ( isOpera ) {
				return;
			}

            $buttons.addClass( 'swp-voice-search-button--supported' );

            $buttons.each( function() {
                const $button = $( this );
                const $form = $button.closest( 'form' );
                const formId = $form.attr( 'id' );
                if ( formId ) {
                    $button.data( 'aria-label-idle', $button.attr( 'aria-label' ) );
                }
            } );

            $buttons.on( 'click', function(e) {
                e.preventDefault();

                const $button = $( this );
                const $form  = $button.closest( 'form' );
                const formId = $form.attr( 'id' );
                if ( ! formId ) {
                    return;
                }

                const activeFormId = VoiceSearchState.getActiveFormId();
                if ( activeFormId && activeFormId !== formId ) {
                    VoiceSearchState.resetToIdle( activeFormId );
                }

                // If already listening, abort the recognition.
                if ( VoiceSearchState.getState( formId ) === 'listening' ) {
                    VoiceSearchState.resetToIdle( formId );
                    return;
                }

                VoiceSearchState.setSpeechStarted( formId, false );
                VoiceSearchState.cleanup( formId );

                const $input         = $form.find( '.swp-input--search' );
                const autoSubmit     = Number( $form.data( 'voiceSearchAutoSubmit' ) ) === 1;
                const interimResults = Number( $form.data( 'voiceSearchInterimResults' ) ) === 1;
                const timeoutMs      = parseInt( $form.data( 'voiceSearchTimeout' ), 10 ) || 5000;

                const recognition           = new SpeechRecognition();
                recognition.lang            = document.documentElement.lang || 'en-US';
                recognition.interimResults  = interimResults;
                recognition.maxAlternatives = 1;
                recognition.continuous      = true;

                VoiceSearchState.setRecognition( formId, recognition );
                VoiceSearchState.setState( formId, 'listening', { recognition } );

                recognition.onresult = ( event ) => {

                    let finalTranscript   = '';
                    let interimTranscript = '';

                    for ( let i = 0; i < event.results.length; i++ ) {
                        const result = event.results[ i ];
                        if ( result.isFinal ) {
                            finalTranscript += result[ 0 ].transcript;
                        } else {
                            interimTranscript += result[ 0 ].transcript;
                        }
                    }

                    const fullTranscript = ( finalTranscript + interimTranscript ).trim();
                    const lastResult = event.results[ event.results.length - 1 ];

                    if ( lastResult.isFinal ) {
                        if ( ! fullTranscript.length ) {
                            VoiceSearchState.showTooltip( formId, VoiceSearchState.getTooltipMessage( 'empty-transcript' ), 'error' );
                            VoiceSearchState.resetToIdle( formId );
                            return;
                        }

                        VoiceSearchState.setState( formId, 'processing', { lastTranscript: fullTranscript } );
                        $input.val( fullTranscript );
                        $input.trigger( 'input' );
                        if ( autoSubmit ) {
                            $form.submit();
                        }
                        setTimeout( () => {
                            VoiceSearchState.setSpeechStarted( formId, false );
                            VoiceSearchState.cleanup( formId );
                        }, 100 );
                    } else {
                        if ( ! VoiceSearchState.hasSpeechStarted( formId ) ) {
                            VoiceSearchState.setSpeechStarted( formId, true );
                        }
                        VoiceSearchState.resetTimeout( formId, timeoutMs );
                        if ( interimResults ) {
                            $input.val( fullTranscript );
                        }
                    }
                };

                recognition.onerror = ( event ) => {
                    const entry = VoiceSearchState.forms[ formId ];

                    // Skip "aborted" errors when we intentionally stopped recognition.
                    if ( event.error === 'aborted' && entry && entry.intentionalStop ) {
                        entry.intentionalStop = false;
                        return;
                    }

                    // Also skip if we're already in a terminal state (processing/error).
                    const currentState = VoiceSearchState.getState( formId );
                    if ( currentState === 'processing' || currentState === 'error' ) {
                        return;
                    }

                    console.error( 'Voice recognition error:', event.error );
                    VoiceSearchState.clearTimeout( formId );
                    VoiceSearchState.showTooltip( formId, VoiceSearchState.getTooltipMessage( event.error ), 'error' );
                    VoiceSearchState.resetToIdle( formId );
                };

                recognition.onend = () => {
                    VoiceSearchState.clearTimeout( formId );
					VoiceSearchState.resetToIdle( formId );
					//recognition.stop();
                };

                recognition.start();

                const timeoutId = setTimeout( () => {
                    // Only stop if speech hasn't started yet.
                    if ( ! VoiceSearchState.hasSpeechStarted( formId ) ) {
                        VoiceSearchState.showTooltip( formId, VoiceSearchState.getTooltipMessage( 'no-speech' ), 'error' );
                        VoiceSearchState.clearTimeout( formId );
                        VoiceSearchState.resetToIdle( formId );
                    }
                }, timeoutMs );
                VoiceSearchState.setTimeout( formId, timeoutId );
            } );
        },
    };

    app.init();

    window.searchwp                  = window.searchwp || {};
    window.searchwp.searchForms      = app;
    window.searchwp.voiceSearchState = VoiceSearchState;

}( jQuery ) );
