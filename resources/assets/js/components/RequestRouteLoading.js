import d3 from 'd3';
import React from 'react';
import rd3 from 'react-d3-library';
import classnames from 'classnames';

import requestRouteLoadingStyles from '../../sass/components/requestRouteLoading.scss';


const RD3Component = rd3.Component;



let D3Config = {
    
    width: 478,
    height: 200,
    routeColors: [
        'red',
        'green',
        'blue',
        'yellow',
        'cyan',
        'magenta'
    ]

}






class RequestRouteLoading extends React.Component {

    createD3Element(data) {

        let node = document.createElement('div');
        let stationsCount = 2;
       
        let svgContainer = d3.select(node)
            .append("svg")
            .attr("width", D3Config.width)
            .attr("height", D3Config.height);

             
        let stationGroup = svgContainer.append("g")
            .attr("transform", "translate(0,0)")
            .classed("station-elements", true);

        // Add the first station
        let sourceStationSubGroup = stationGroup.append("g")
            .attr("transform", "translate(30,50)")
            .classed("station-element", true);


        let sourceStationCircle = sourceStationSubGroup.append("circle")
            .attr("cx", "15")
            .attr("cy", "15")
            .attr("r", "15")
            .style("fill", "white")
            .style("stroke", "black")
            .style("stroke-width",  "3");


        let sourceStationsName = sourceStationSubGroup.append("text")
            .attr("x", "15")
            .attr("y", "15")
            .attr("dx", "-7")
            .attr("dy", "7")
            .attr("r", "15")
            .style("font-size", "19")
            .style("font-weight", "bold")
            .style("font-family",  "Open Sans")
            .text(this.props.source_station);




        let destinationStationSubGroup = stationGroup.append("g")
            .attr("transform", "translate(" + D3Config.width - 60 + ", 30)")
            .classed("station-element", true);


        let destinationStationCircle = destinationStationSubGroup.append("circle")
            .attr("cx", "15")
            .attr("cy", "15")
            .attr("r", "15")
            .style("fill", "white")
            .style("stroke", "black")
            .style("stroke-width",  "3");


        let destinationStationName = destinationStationSubGroup.append("text")
            .attr("x", "15")
            .attr("y", "15")
            .attr("dx", "-7")
            .attr("dy", "7")
            .attr("r", "15")
            .style("font-size", "19")
            .style("font-weight", "bold")
            .style("font-family",  "Open Sans")
            .text(this.props.destination_station);

      

        

        let routeGroup = svgContainer.append("g")
            .attr("transform", "translate(30,30)")
            .classed("route-elements", true);


        let stationLine= routeGroup.append("line")
                .attr("x1", "30")
                .attr("y1", "15")
                .attr("x2", D3Config.width - 60 - 30 )
                .attr("y2", "15")
                .style("stroke", D3Config.routeColors[Math.round(Math.random()*5)])
                .style("stroke-width",  "3");


        let stationLineHint = routeGroup.append("text")
            .attr("x", (D3Config.width - 60 - 30)/2 - 20 )
            .attr("y", "25")
            .attr("dx", "-7")
            .attr("dy", "7")
            .style("font-size", "24")
            .style("font-weight", "bold")
            .style("font-family",  "Open Sans")
            .text("Optimising Route");


        return node;
 
    }



    render() {

    	// let node = this.createD3Element(this.props.data);

        return (
            <div>
            	<svg width={ D3Config.width } height={ D3Config.height }   className={ classnames(requestRouteLoadingStyles.routeRequestLoading) } >
            		<g  transform="translate(0,0)"   className={ classnames(requestRouteLoadingStyles.stationElements) } >
            			
            			<g  transform="translate(0,50)"  className={ classnames(requestRouteLoadingStyles.stationElement) }  >
            				
            				<circle cx="15" cy="15" r="15" fill="red" stroke="red" strokeWidth="0" >
            				</circle>

            				<text x="15" y="15" dx="-7" dy="7"  className={ classnames(requestRouteLoadingStyles.textElement) }  >
            					{ this.props.source_station }
            				</text>
            			</g>

            			<g  transform={ "translate(448,50)" }   className={ classnames(requestRouteLoadingStyles.stationElement) } >
            				<circle cx="15" cy="15" r="15" fill="red" stroke="red" strokeWidth="0" >
            				</circle>

            				<text x="15" y="15" dx="-7" dy="7"  className={ classnames(requestRouteLoadingStyles.textElement) }  >
            					{ this.props.destination_station }
            				</text>
            			</g>

            		</g>

            		{/* <g  transform="translate(0,0)"   className={ classnames(requestRouteLoadingStyles.lineElements) } >
            			
            			<g  transform="translate(30, 50)"  className={ classnames(requestRouteLoadingStyles.lineElement) } >
            				<line x1="30" y1="15" x2={ D3Config.width - 60 - 30 } y2="15" stroke={ D3Config.routeColors[Math.round(Math.random()*5)] }  strokeWidth="3" >
            				</line>

            				<text x={ (D3Config.width - 60 - 30)/2 - 20 }  y="35" dx="-7" dy="7"  className={ classnames(requestRouteLoadingStyles.textElement) }  >
            					Optimising Route
            				</text>
            			</g>

            		</g> */}
            	</svg>
            </div>
        );
    }


}




export default RequestRouteLoading;


