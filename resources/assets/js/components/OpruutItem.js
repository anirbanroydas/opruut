import React from 'react';

import {Card, CardActions, CardHeader, CardMedia, CardTitle, CardText} from 'material-ui/Card';
import FlatButton from 'material-ui/FlatButton';
import RaisedButton from 'material-ui/RaisedButton';
// import Toggle from 'material-ui/Toggle';
import Divider from 'material-ui/Divider';
import classnames from 'classnames';


import OpruutHeart from './OpruutHeart';
import RouteTable from './RouteTable';
import RouteD3Element from './RouteD3Element';

import opruutItemStyles from '../../sass/components/opruutItem.scss';



let customStyle = {
	borderRadius: "3px",
	boxShadow: "0px",
	border: "1px solid #e8e8e8"
}




class OpruutItem extends React.Component {

	constructor(props) {
	  	super(props);
	
	  	this.getPreference = this.getPreference.bind(this);
	  	this.showDetails = this.showDetails.bind(this);

	  	let routes = this.props.routes;
		let routes_state = [];
		let route_numbers = [];
		// console.log('[OpruutItem] constructor : route_numbers  : ', route_numbers);
		for (let i=0; i<routes.length; ++i) {
			routes_state.push({isOpen: false, buttonLabel: 'Show Route Details'});
			route_numbers.push({show: false});
		}
		// console.log('[OpruutItem] constructor : route_numbers after loop : ', route_numbers);
		// console.log('[OpruutItem] constructor : route_numbers[0] after loop : ', route_numbers[0]);
		let route_no_0 = route_numbers[0];
		// console.log('[OpruutItem] constructor : route_no_0 after loop : ', route_no_0);
		
		// console.log('[OpruutItem] constructor : route_numbers after change : ', route_numbers);

	  	this.state = {
	  		routes: routes_state,
	  		route_no: route_numbers
	  	};

	  	// console.log('[OpruutItem] constructor : state  : ', this.state);
	}




	renderTravelTime(t) {
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





	getPreference() {
	 	switch (this.props.preference) {
	 		case 0:
	 			return 'No Preference';
	 		case 1:
	 			return 'Time';
	 		case 2:
	 			return 'Comfort';
	 		case 3:
	 			return 'Both';
	 	}
	}




	showDetails(route_no, e) {
		e.preventDefault();
		let routes_state = this.state.routes;
		let isOpened = routes_state[route_no].isOpen;
		let button_label = '';
		if (isOpened) {
			button_label = 'Show Route Details';
		}
		else {
			button_label = 'Hide Route Details';
		}
		routes_state[route_no] = {isOpen: !isOpened, buttonLabel: button_label};

		this.setState({routes: routes_state});
	}




	showOtherRoute(route_no, e) {
		// console.log('[OpruutItem] showOtherRoute : state  : ', this.state);
		e.preventDefault();
		let route_no_state = this.state.route_no;
		// console.log('[OpruutItem] showOtherRoute : route_no_state  : ', route_no_state);
		route_no_state[route_no] = {show: !route_no_state[route_no].show};

		this.setState({route_no: route_no_state});
		
	}





	renderOtherRoutes() {

		let other_routes = [];
		
		for (let i=1; i < this.props.routes.length; ++i) {

			other_routes.push(
				<div key={i}>
					<FlatButton 
	    				label={ `Route #${i+1}` } 
	    				secondary={false} 
	    				style={{marginTop: "10px", marginBottom: "10px"}} 
				 		labelStyle={ {textTransform: "none"} } 
				 		onClick={this.showOtherRoute.bind(this, i)}
	    			/>


	    			{ 
	    				this.state.route_no[i].show ? 
	    					<CardText expandable={false}>

			        			{ this.renderOptimizedRoute(i) } 	
			        
			        		</CardText>
			        	
			        	: null 
			       	}

	    			<Divider inset={false} />
	    		</div>					
	    	);
						
		}


		return (
			<div>
				{ other_routes }	
    		</div>

		);


	}






	renderOptimizedRoute(route_no) {

		// // console.log(`[OpRuutItem] renderOptimizedRoute : route_not : ${route_no} | this.state : ${this.state} | this.state.routes[route_no].buttonLable : ${this.state.routes[route_no].buttonLabel}`);

		if (this.props.routes.length > 0) {
			return (
				<div>

        			<FlatButton 
        				label={ `Interchanges: ${this.props.routes[route_no].interchanges}` } 
        				secondary={true} 
        				style={{marginTop: "0px"}} 
				 		labelStyle={ {textTransform: "none"}} 
        			/>
				    <FlatButton 
				    	label={`Stations: ${this.props.routes[route_no].station_count}`} 
				    	primary={true} 
				    	style={{marginTop: "0px"}} 
				 		labelStyle={ {textTransform: "none"}} 

				    />
				    <FlatButton 
				    	label={`Distance: ${Math.round(this.props.routes[route_no].travel_distance*100)/100} km`} 
				    	secondary={true} 
				    	style={{marginTop: "0px"}} 
				 		labelStyle={ {textTransform: "none"}} 
				    />
				    <FlatButton 
				    	label={`Time: ${this.renderTravelTime(this.props.routes[route_no].travel_time)}`} 
				    	primary={true} 
				    	style={{marginTop: "0px"}} 
				 		labelStyle={ {textTransform: "none"}} 
				    />

				    <RouteD3Element data={this.props.routes[route_no]} /> 

				    {/* 
				    	<svg height="80" width="500">
						  	<line x1="2" y1="50" x2="80" y2="50" stroke="green" strokeWidth="6" />
						  
						  	<circle cx="90" cy="50" r="10" stroke="black" strokeWidth="4" fill="white" />
						  	<line x1="101" y1="50" x2="140" y2="50" stroke="yellow" strokeWidth="6" />
						  	<circle cx="150" cy="50" r="10" stroke="black" strokeWidth="4" fill="white" />
						  	<line x1="160" y1="50" x2="290" y2="50" stroke="blue" strokeWidth="6" />
						  	<circle cx="300" cy="50" r="10" stroke="black" strokeWidth="4" fill="white" />
						</svg>
					*/}


					<RaisedButton 
					 	label={ `${this.state.routes[route_no].buttonLabel}` }
					 	primary={true} 
					 	style={{marginTop: "25px"}} 
					 	labelStyle={ {textTransform: "none"}} 
					 	onClick={this.showDetails.bind(this, route_no)}
					/>

					{ this.state.routes[route_no].isOpen ? <RouteTable data={this.props.routes[route_no]} /> : null }

				</div>
			);
		
		}
		else {

			return ( 
				<CardTitle subtitle={ `Oops! No routes are available for this pair` } expandable={false} />
			);
		}

	}







	render() {
		
		return (
			<div className={ classnames(opruutItemStyles.opruutItemContainer)} >
				<Card  style={customStyle}>
			        <CardHeader
			          	title={this.props.userName ? this.props.userName : "Anonymous"}
			          	subtitle={this.props.userUsername ? `@${this.props.userUsername}  \u00b7  ${this.props.created_at_humans}` : '@IHaventRegistered'}
			          	avatar={this.props.avatar ? this.props.avatar : ''}   // "media/avatars/avatar_female_10.jpeg"
			          	actAsExpander={false}
			          	showExpandableButton={false}

			        />	

			        <CardMedia
			          	expandable={false}
			          	overlay={ <CardTitle title={ `${this.props.city} Metro` } subtitle={ `${this.props.from} to ${this.props.to}` } /> }
			        >
			          	
			          	<img src={ this.props.cityImg ? this.props.cityImg : "http://www.material-ui.com/images/nature-600-337.jpg" } />
			        
			        </CardMedia>
			        
			        <CardTitle title="Optimised Route" subtitle={ `Preference: ${this.getPreference()}  \u00b7  Ride Time: ${this.props.ride_time_tz}` } expandable={false} />
			        
			        <CardText expandable={false}>

			        	{ this.renderOptimizedRoute(0) } 	
			        
			        </CardText>

			        <Divider inset={false} />

			        { 
			        	this.props.routes.length > 0 ? 
			        		<CardTitle subtitle="Other routes" expandable={false} actAsExpander={true} showExpandableButton={true} />

			        	: null 
			        }
			        
			        <CardText expandable={true}>

			        	{ this.renderOtherRoutes() }

			        </CardText>

			        <CardActions>
					   
					   <OpruutHeart
			              	authenticated={this.props.authenticated}
			              	isFavorited={ this.props.isFavorited}
			              	favorites_count={this.props.favorites_count}
			              	opruutId={this.props.opruutId}
			              	toggleFavorite={ (selectedOpruut, isFavorited) => this.props.toggleFavorite(selectedOpruut, isFavorited) }
			            />

			        </CardActions>
			      
			    </Card>
			</div>
		);
	}
}




OpruutItem.propTypes = {

	opruutId: React.PropTypes.number,
	isFavorited: React.PropTypes.bool,
	favorites_count: React.PropTypes.number,
	ride_time_tz: React.PropTypes.string,
	created_at_humans: React.PropTypes.string,
	userName: React.PropTypes.string,
	userUsername: React.PropTypes.string,
	avatar: React.PropTypes.string,
	from: React.PropTypes.string,
	to: React.PropTypes.string,
	routes: React.PropTypes.array,
	preference: React.PropTypes.number,
	city: React.PropTypes.string,
	cityImg: React.PropTypes.string,
	authenticated: React.PropTypes.bool,
	toggleFavorite: React.PropTypes.func

};


export default OpruutItem;

