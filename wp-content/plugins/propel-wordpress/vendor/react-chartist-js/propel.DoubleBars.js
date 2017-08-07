var React = require("react");
var ReactDOM = require('react-dom');
var ChartistGraph = require('react-chartist').default;

class DoubleBars extends React.Component {
  render() {
 
    var data = {
      labels: ['W1', 'W2', 'W3'],
      series: [
        [-2, -1, -3],
        [2, 1, 3],
        [6, 7, 5]
      ]
    };
 
    var options = {
      high: 12,
      low: -12,
      onlyInteger: true,
      divisor: 2,
      axisX: {
       onlyInteger: true,
       labelInterpolationFnc: function(value, index) {
          // return index % 2 === 0 ? value : null;
          return value;
        }
      },
      stackBars: true,
      horizontalBars: true
    };
 
    var type = 'Bar'
 
    return (
      <div>
        <ChartistGraph data={data} options={options} type={type} />
      </div>
    )
  }
}

module.exports = DoubleBars;