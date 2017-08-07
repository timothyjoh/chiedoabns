// Propel Learner Data
// decorates LearnDash Quizzes, sends extra data about learner actions
'use strict';

jQuery( function() { // onload - functions for the quiz
	console.log('PROPEL ready to capture learner data.');
	scitent.learner_data_capture.init_wpapi();
	scitent.learner_data_capture.capture_time();

	jQuery('body').on('click','input[name="wdmcheck"],input[name="next"]',function(e){
		scitent.learner_data_capture.single_answer(e);
	});
});

if(!scitent) { var scitent = {}; }
if(!scitent.utils) { scitent.utils = {}; }

scitent = jQuery.extend({}, scitent, {
	learner_data_capture: {
		wp: {},
		ns: 'scitent/v1',
		start_time: 0,
		capture_time: function() {
			var end_time = Date.now();
			var interval = end_time - this.start_time;
			this.start_time = end_time;
			return interval;
		},
		init_wpapi: function() {
			this.wp = new WPAPI({
			    endpoint: window.WP_PROPEL_API_Settings.endpoint,
			    nonce: window.WP_PROPEL_API_Settings.nonce
			});
			this.wp.learnerStats = this.wp.registerRoute( this.ns, '/learner-stats/(?P<id>\\d+)' );
			this.wp.learnerPostAttempt = this.wp.registerRoute( this.ns, '/learner-post-attempt' );
			// Make GET calls like this:
			// this.wp.learnerStats().id( 20 ).get(function(err,data) {
			// 	if(err){
			// 		console.log('could not get that');
			// 	} else {
			// 		console.log(data);
			// 	}
			// });
			//
			// Make POST calls like this:
			// this.wp.learnerPostAttempt().id( 20 ).create({
			// 	data: 'stuff'
			// }).then(function( response ) {
			// 	console.log( response );
			// });
		},
		single_answer: function(e) { // provide quiz_id, question_id, response_content, response_time
			var $questionList = jQuery(e.target).closest('.wpProQuiz_listItem').find('.wpProQuiz_questionList')
			var quiz_id = $questionList.closest('.wpProQuiz_content').attr('id').slice('wpProQuiz_'.length);
			var question_id = $questionList.attr('data-question_id');
			var response_content = jQuery.map($questionList.children(),function(val,i){
				return jQuery(val).find('input').is(':checked') ? 1 : 0;
			});
			var response_time = 0; // TODO: calculate timings
			this.wp.learnerPostAttempt().create({ // POST
				quiz_id : quiz_id,
				question_id : question_id,
				context : e.target.value,
				response_content : response_content,
				response_time : this.capture_time()
			}).then(function( response ) {
				// console.log( response );
			});
		}
	}
});