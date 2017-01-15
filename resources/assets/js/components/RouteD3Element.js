const d3 =  require('d3');
import React from 'react';
import rd3 from 'react-d3-library';

import { ucwords } from '../utils/strings';

import rd3ComponentStyles from '../../sass/components/rd3Component.scss';
// console.log('[RD3] rd3ComponentStyles : ', rd3ComponentStyles);

const RD3Component = rd3.Component;

const d3tip = require('d3-tip');
// console.log('[RD3] d3tip : ', d3tip);






let D3Config = {
    
    width: 566,
    height: 130,
    routeColors: [
        'red',
        'green',
        'blue',
        'yellow',
        'cyan',
        'magenta'
    ]

}



// <svg height="80" width="500">
//     <line x1="2" y1="50" x2="80" y2="50" stroke="green" strokeWidth="6" />
  
//     <circle cx="90" cy="50" r="10" stroke="black" strokeWidth="4" fill="white" />
//     <line x1="101" y1="50" x2="140" y2="50" stroke="yellow" strokeWidth="6" />
//     <circle cx="150" cy="50" r="10" stroke="black" strokeWidth="4" fill="white" />
//     <line x1="160" y1="50" x2="290" y2="50" stroke="blue" strokeWidth="6" />
//     <circle cx="300" cy="50" r="10" stroke="black" strokeWidth="4" fill="white" />
// </svg>






class RouteD3Element extends React.Component {

    constructor(props) {
        super(props);
      
        // this.state = {
        //     data: ''
        // };
    }





    createRouteD3Element() {

        let data = this.props.data;

        // first find the number of interchanges required for this route
        let interchanges = data.interchanges;
        let interchanges_stations = data.interchanges_stations;
        let interchangeLengths = [];
        
        let j = 0;
        let interchange_distance = 0;

        for (let i=0; i<data.routes.length; ++i) {
            interchange_distance = interchange_distance + data.routes[i].distance;
            
            if (j < interchanges_stations.length && interchanges_stations[j] === i+1) {
               interchangeLengths[j] =  interchange_distance;
               ++j;
               interchange_distance = 0;
            }
        }

        interchangeLengths[j] = interchange_distance;

        // console.log('[RD3] travel_distance : ', data.travel_distance);
        // console.log('[RD3] interchanges : ', interchanges);
        // console.log('[RD3] interchangeLengths : ', interchangeLengths);

        let node = document.createElement('div');
        // console.log('[RD3] createD3Element node : ', node);
        // console.log('[RD3] rd3 : ', rd3);
        // console.log('[RD3] d3 : ', d3);
        let stationsCount = data.stations.length;

        
        let range_max = D3Config.width - ((interchanges+2)*30) - 100;     
        
        // console.log('[RD3] range_max : ', range_max);  

        // Linear Scale of distances
        let scaledRouteLength = d3.scaleLinear()
            .domain([0, data.travel_distance])
            .range([0, range_max]);

        
        // console.log('[RD3] scaledRouteLength(0) : ', scaledRouteLength(0));
        // console.log('[RD3] scaledRouteLength(2) : ', scaledRouteLength(2));
        // console.log('[RD3] scaledRouteLength(5) : ', scaledRouteLength(5));
        // console.log('[RD3] scaledRouteLength(10) : ', scaledRouteLength(10));
        // console.log(`[RD3] scaledRouteLength(Travel_Distance:${data.travel_distance}) : `, scaledRouteLength(data.travel_distance));

        for (let interchange of interchangeLengths) {
            // console.log(`[RD3] scaledRouteLength(${interchange}) :  ${scaledRouteLength(interchange)}`);
        }


        let tip = d3tip()
            .attr('class', rd3ComponentStyles.d3Tip)
            .offset([-15, 0])
            .html(function(d) {
                // console.log('[Rd3] html : d', d);
                if (d.type === 'station') {
                    return "<strong>Name:</strong> <strong style='color:red'> " + d.name + "</strong> </br>\
                    <strong>isJuntion:</strong> <strong style='color:red'> " + d.isJuntion + "</strong>";
                }
                else {
                    return "<strong  style='color:magenta'>Line:</strong> <strong> " + d.line + "</strong>  </br>\
                    <strong  style='color:magenta'>Distance:</strong> <strong> " + d.distance + " km</strong>";
                }
                
            });

        // console.log('[RD3] tip : ', tip);       

        let svgClass = '';
        // console.log('[RD3] svgClass : ', svgClass);
        // console.log('[RD3] data.rank : ', data.rank);
        if (data.rank > 1) {
            // console.log('[RD3] data.rank > 1 : ');
            svgClass = rd3ComponentStyles.rd3SvgContainerOthers;
        }
        else {
            // console.log('[RD3] data.rank not > 1 : ');
            svgClass = rd3ComponentStyles.rd3SvgContainerFirst;
        }

        // console.log('[RD3] final svgClass : ', svgClass);

        // SVG Container
        let svgContainer = d3.select(node)
            .append("svg")
            .attr("height", D3Config.height)
            .attr("width", D3Config.width)
            .classed(svgClass, true)
            .call(tip);
            // .attr("viewBox", "0 0 "+ D3Config.width + " "+ D3Config.height);
            
        
        // console.log('[RD3] svgContainer : ', svgContainer);
        // console.log('[RD3] svgContainer : ', svgContainer);

       // svgContainer.call(tip);
        // tip(svgContainer);
       
        

        // console.log('[RD3] svgContainer : ', svgContainer);

        // Staions Group
        let stationGroup = svgContainer.append("g")
            .attr("transform", "translate(50, 50)")
            .classed("station-elements", true);

        // console.log('[RD3] stationGroup : ', stationGroup);
        
        let sobj = {
            type: 'station',
            name: ucwords(data.stations[0].name),
            isJuntion: data.stations[0].isJuntion ? 'Yes' : 'No'
        };
        
        // Add the first station inside a Group inside Stations Group
        let stationSubGroup = stationGroup.append("g")
            .data([sobj])
            .attr("transform", "translate(0,0)")
            .classed("station-element", true)
            .on('mouseover', function(d) {
                tip.show(d);
            })
            .on('mouseout', function(d) {
                tip.hide(d);
            });

        // console.log('[RD3] stationSubGroup : ', stationSubGroup);


        let stationsFirstCircle = stationSubGroup.append("circle")
            .attr("cx", "15")
            .attr("cy", "15")
            .attr("r", "15")
            .classed(rd3ComponentStyles.rd3Station, true);
            

        // console.log('[RD3] stationsFirstCircle : ', stationsFirstCircle);

        let stationsFirstName = stationSubGroup.append("text")
            .attr("x", "15")
            .attr("y", "15")
            .attr("dx", "-4")
            .attr("dy", "4")
            .classed(rd3ComponentStyles.rd3StationName, true)
            //.style("font-size", "19")
            // .style("font-weight", "bold")
            // .style("font-family",  "Open Sans")
            .text(data.stations[0].name[0].toUpperCase());

        // console.log('[RD3] stationsFirstName : ', stationsFirstName);

        let cumulativeOffset = 0;

        /// Add the rest of the stations
        for (let i=0; i < interchanges + 1; ++i) {

            cumulativeOffset = cumulativeOffset + scaledRouteLength(interchangeLengths[i]) + 30;
            // console.log('[RD3] Loop : cumulativeOffset : ', cumulativeOffset);

            let station_name = 'M';
            let isJuntion = 'No';
            if (i === interchanges) {
                station_name = data.stations[data.stations.length-1].name;
                isJuntion = data.stations[data.stations.length-1].isJuntion;
            } 
            else {   
                station_name = data.stations[interchanges_stations[i]].name;
                isJuntion = data.stations[interchanges_stations[i]].isJuntion;
            }

            let sobj = {
                type: 'station',
                name: ucwords(station_name),
                isJuntion: isJuntion ? 'Yes' : 'No'
            };

            let stationSubGroup = stationGroup.append("g")
                .data([sobj])
                .attr("transform", "translate(" + cumulativeOffset + ", 0)")
                .classed("station-element", true)
                .on('mouseover', function(d) {
                    tip.show(d);
                })
                .on('mouseout', function(d) {
                    tip.hide(d);
                });

            // console.log('[RD3] Loop : stationSubGroup : ', stationSubGroup);


            let stationCircle = stationSubGroup.append("circle")
                .attr("cx", "15")
                .attr("cy", "15")
                .attr("r", "15")
                .classed(rd3ComponentStyles.rd3Station, true);
                // .style("fill", "white")
                // .style("stroke", "black")
                // .style("stroke-width",  "3");


            // console.log('[RD3] Loop : stationCircle : ', stationCircle);

            

            let stationName = stationSubGroup.append("text")
                .attr("x", "15")
                .attr("y", "15")
                .attr("dx", "-4")
                .attr("dy", "4")
                .classed(rd3ComponentStyles.rd3StationName, true)
                //.style("font-size", "19")
                // .style("font-weight", "bold")
                // .style("font-family",  "Open Sans")
                .text(station_name[0].toUpperCase());

            // console.log('[RD3] Loop : stationName : ', stationName);
        }

        


        let routeGroup = svgContainer.append("g")
            .attr("transform", "translate(50,50)")
            .classed("route-elements", true);


        cumulativeOffset = 0;

        /// Add the station routes
        for (let i=0; i < interchanges + 1; i++) {

            if (i === 0) {
                cumulativeOffset = 31;
            }
            else {
                cumulativeOffset = cumulativeOffset + scaledRouteLength(interchangeLengths[i-1]) + 30;
            }

            let route_color = 'grey';

            if (i === interchanges ) {
                route_color = data.routes[data.routes.length-1].line;
            }
            else  {
                route_color = data.routes[interchanges_stations[i]-1].line;
            }

            route_color = route_color.split('_')[0];


            let lobj = {
                type: 'route',
                line: route_color,
                distance: Math.round(interchangeLengths[i]*100)/100
            };

            let routeSubGroup = routeGroup.append("g")
                .data([lobj])
                .attr("transform", "translate(" + cumulativeOffset + ", 0)")
                .classed("route-element", true)
                .on('mouseover', function(d) {
                    tip.show(d);
                })
                .on('mouseout', function(d) {
                    tip.hide(d);
                });

            

            let stationLine= routeSubGroup.append("line")
                .attr("x1", "0")
                .attr("y1", "15")
                .attr("x2", scaledRouteLength(interchangeLengths[i]) - 2)
                .attr("y2", "15")
                .style("stroke", route_color)
                .style("stroke-width",  "2");

        }


        return node;
 
    }



    // componentWillMount() {

    //     let node = this.createD3Element(this.props.data);
    
    //     this.setState({data: node});
  
    // }



    render() {
        let node = this.createRouteD3Element();
        // console.log('[RD3] render() called :  node ', node);
        return (
            <RD3Component data={node} />
        );
    }


}




RouteD3Element.propTypes = {

    data: React.PropTypes.object,

};




export default RouteD3Element;


