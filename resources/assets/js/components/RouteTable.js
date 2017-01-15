import React from 'react';
import classnames from 'classnames';

import {Table, TableBody, TableFooter, TableHeader, TableHeaderColumn, TableRow, TableRowColumn}
  from 'material-ui/Table';


// import routeTableStyles from '../../sass/components/RouteTable.scss';



let customStyles = {
  	propContainer: {
    	width: 200,
    	overflow: 'hidden',
    	margin: '20px auto 0',
  	},
  	propToggleHeader: {
    	margin: '20px auto 7px 7px',
  	},

};





class RouteTable extends React.Component {

	constructor(props) {
	  	super(props);

	  	this.state = {
	      	height: '300px',
	    };
	}




	travelTime(travel_distance, station_count=1) {
		// console.log('travelTime : args(travel_distance, station_count) : ', travel_distance, station_count);
        let wait_time = station_count*20; // in seconds
        let route_time = (travel_distance/40)*60*60; //in seconds
        let total_time = wait_time + route_time; // in seconds
        
        let travel_secs = parseInt(total_time%60);
        let travel_hours = parseInt((total_time/60)/60);
        let travel_mins = parseInt((total_time/60)%60);       

        return { 'hours': travel_hours, 'mins': travel_mins, 'secs': travel_secs, 'total_time_secs': total_time };
    }



    timeForHumans(t) {
    	let r = '';
    	if (t.hours !== 0) {
    		r = `${r}${t.hours} h `;
    	}
    	if (t.mins !== 0) {
    		r =  `${r}${t.mins} m `;
    	}
    	if (t.secs !== 0) {
    		r =  `${r}${t.secs} s`;
    	}

    	return r; 
    }


	renderRows() {
		// console.log('renderRows : ');
		let stations = this.props.data.stations;
		// console.log('renderRows : STATIONS : ', stations);
		let routes = this.props.data.routes;
		// console.log('renderRows : ROUTES : ', routes);
		let Rows = [];
		let line = '-'
		let distance = '-';
		let cumulative_distance = '-';
		let time = '-';
		let time_for_humans = '-';
		let cumulative_time = '-';
		let cumulative_time_for_humans = '-';

		for (let i=0; i < stations.length; ++i) {


			let row = (

				<TableRow key={i} selectable={false} style={ {height: "auto"} }>
					<TableRowColumn style={ {width: "28%", padding: "5px 5px 5px 5px"} } >{stations[i].name}</TableRowColumn>
					<TableRowColumn style={ {width: "11%", padding: "5px 5px 5px 5px"} } >{line}</TableRowColumn>
					<TableRowColumn style={ {width: "11%", padding: "5px 5px 5px 5px"} } >{ distance }</TableRowColumn>
					<TableRowColumn style={ {width: "16%", padding: "5px 5px 5px 5px"} } >{ cumulative_distance }</TableRowColumn>
					<TableRowColumn style={ {width: "15%", padding: "5px 5px 5px 5px"} } >{time_for_humans}</TableRowColumn>
					<TableRowColumn style={ {width: "19%", padding: "5px 5px 5px 5px"} } >{cumulative_time_for_humans}</TableRowColumn>
				</TableRow>

			);

			Rows.push(row);

			if (i === stations.length - 1) {
				break;
			}
			if (i === 0) {
				distance = 0;
				cumulative_distance = 0;
				time = 0;
				line = null;
				time_for_humans = 0;
				cumulative_time = 0;
				cumulative_time_for_humans = 0;
			}

			line = routes[i].line;
			// console.log('renderRows : routes[i].distance : ', routes[i].distance);
			distance = Math.round(routes[i].distance*100)/100;
			cumulative_distance = Math.round((cumulative_distance + distance)*100)/100;
			// console.log('renderRows : cumulative_distance : ', cumulative_distance);
			time = this.travelTime(distance);
			// console.log('renderRows : time : ', time);
			time_for_humans = this.timeForHumans(time);
			// console.log('renderRows : time_for_humans : ', time_for_humans);
			cumulative_time = this.travelTime(cumulative_distance, i+1);
			// console.log('renderRows : cumulative_time : ', cumulative_time);
			cumulative_time_for_humans = this.timeForHumans(cumulative_time);
			// console.log('renderRows : cumulative_time_for_humans : ', cumulative_time_for_humans);
			
			
		}


		return Rows;
	}




	render() {
		
		return (
			<div >
				<Table
					height={this.state.height}
					fixedHeader={true}
					selectable={false}
					multiSelectable={false}
					style={ { tableLayout: "auto"}}
				>

					
				

					<TableHeader
						displaySelectAll={false}
						adjustForCheckbox={false}
						enableSelectAll={false}
					>
						<TableRow>
							<TableHeaderColumn tooltip="" style={ {width: "28%", padding: "5px 5px 5px 5px"} } >Station</TableHeaderColumn>
							<TableHeaderColumn tooltip="" style={ {width: "11%", padding: "5px 5px 5px 5px"} } >Line</TableHeaderColumn>
							<TableHeaderColumn tooltip="" style={ {width: "11%", padding: "5px 5px 5px 5px"} } >Distance</TableHeaderColumn>
							<TableHeaderColumn tooltip="" style={ {width: "16%", padding: "5px 5px 5px 5px"} } >Distance Until</TableHeaderColumn>
							<TableHeaderColumn tooltip="" style={ {width: "15%", padding: "5px 5px 5px 5px"} } >Time</TableHeaderColumn>
							<TableHeaderColumn tooltip="" style={ {width: "19%", padding: "5px 5px 5px 5px"} } >Time Until</TableHeaderColumn>
						</TableRow>
					</TableHeader>

				

				
				
					<TableBody
						displayRowCheckbox={false}
						deselectOnClickaway={false}
						showRowHover={true}
						stripedRows={false}
					>
					

					

						{ this.renderRows() }


					</TableBody>
				</Table>
			</div>
		);
	}
}




RouteTable.propTypes = {

	data: React.PropTypes.object,

};


export default RouteTable;








