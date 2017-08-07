var React = require("react");
var ReactDOM = require('react-dom');
var ChartistGraph = require('react-chartist').default;
var DoubleBars = require('./propel.DoubleBars');

class PropelDashboard extends React.Component {
  constructor(props) {
    super(props);
    this.wp = new WPAPI({
      endpoint: window.WP_PROPEL_API_Settings.endpoint,
      nonce: window.WP_PROPEL_API_Settings.nonce
    });
    this.ns = 'scitent/v1',
    this.wp.learnerLatestFirstTries = this.wp.registerRoute( this.ns, '/learner-latest-first-tries/(?P<id>\\d+)' );
    // test:
    this.wp.learnerLatestFirstTries().id( 20 ).create({ // POST
      quiz_id : '',
      question_id : '',
      points : ''
    }).then(function( response ) {
     console.log( response );
    });
  };
  render() {
    return (
      <div>
        <h2>Learner Dashboard</h2>
        <DoubleBars />
      </div>
      );
  };
}
 
ReactDOM.render(<PropelDashboard />, document.getElementById('propel-learner-container') );
