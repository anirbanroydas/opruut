import React from 'react';
import classnames from 'classnames';

// import AutoComplete from 'material-ui/AutoComplete';
import Autocomplete   from 'react-toolbox/lib/autocomplete';
import Dropdown   from 'react-toolbox/lib/dropdown';
import SelectField from 'material-ui/SelectField';
import MenuItem from 'material-ui/MenuItem';
import RaisedButton from 'material-ui/RaisedButton';
import LinearProgress from 'material-ui/LinearProgress';

import RequestRouteLoading from './RequestRouteLoading';
import Spinners from './Spinners';

import opruutRequestBarStyles from '../../sass/components/opruutRequestBar.scss';
import autocompleteTheme from '../../sass/themes/RT_autocomplete.scss';
import dropdownTheme from '../../sass/themes/RT_dropdown.scss';



const styles = {
  	
  	customWidth: {
    	width: 150,
  	},
  	selectfield: {
  		margin: 12
  	},
  	customColor: {
  		backgroundColor: "#fff"
  	}
};




const Preferences = [

    { value: 0, preference: 'No Preference', meaning: 'Surprise me!' },
    { value: 1, preference: 'Time', meaning: 'Travel fast'},
    { value: 2, preference: 'Comfort', meaning: 'Comfortable ride'},
    { value: 3, preference: 'Both', meaning: 'Fast & Comfortable' }
];



function renderRideTimes() {
		
	let startHour = 5;
	let endHour = 23;
	let minInterval = 15;

	let rideTimeItems = [ { value:0, rideTime:"Ride Now" } ];

	for (let h=startHour, m=0, i=1; h<=endHour; m += minInterval, ++i) {
		
		let hour, min, meridian;

		if (m === 60 ) {
			++h;
			m = 0;
		}

		if (m === 0) {
			min = '00';
		}
		else {
			min = `${m}`
		}


		if (h < 10 ) {
			hour = `0${h}`;
		}
		else if (h > 12) {
			hour = `${h-12}`;
		}
		else {
			hour = `${h}`;
		}


		if (h < 11 || h === 24 ) {
			meridian = 'AM'
		}
		else {
			meridian = 'PM';
		}

		rideTimeItems.push( { value:i, rideTime: `${hour}:${min} ${meridian}` } );
	}


	return rideTimeItems;
}




const RideTimes = renderRideTimes();



// comment

class OpruutRequestBar extends React.Component {
		
	
	constructor(props) {
		super(props);
		
		this.renderRideTimeItems = this.renderRideTimeItems.bind(this);
		this.handleSourceSearchQueryOnChange = this.handleSourceSearchQueryOnChange.bind(this);
		this.handleDestinationSearchQueryOnChange = this.handleDestinationSearchQueryOnChange.bind(this);
		this.handleSourceStationOnChange = this.handleSourceStationOnChange.bind(this);
		this.handleDestinationStationOnChange = this.handleDestinationStationOnChange.bind(this);
		this.submitRequest = this.submitRequest.bind(this);
		this.handlePreferenceChange = this.handlePreferenceChange.bind(this);
		this.handleRideTimeChange = this.handleRideTimeChange.bind(this);	
		this.handleSourceFocus = this.handleSourceFocus.bind(this);
		this.handleDestinationFocus = this.handleDestinationFocus.bind(this);
		this.customPreferenceItem = this.customPreferenceItem.bind(this);
		this.customRideTimeItem = this.customRideTimeItem.bind(this);
		
		this.state = {
			
			city: null,
			source_station: '',
			destination_station: '',
			station_list: [],

			completed: 0,	
			progressCompleted: true,
			preference: 0,
			rideTime: 0,
			preferenceError: '',
			rideTimeError: '',
			destinationStationError: '',
			sourceStationError: ''			
		}

		

	}




	renderRideTimeItems() {
		let startHour = 5;
		let endHour = 23;
		let minInterval = 15;

		let rideTimeItems = [ <MenuItem key={0} value={0} primaryText="Ride Now" /> ];

		for (let h=startHour, m=0, i=1; h<=endHour; m += minInterval, ++i) {
			
			let hour, min, meridian;

			if (m === 60 ) {
				++h;
				m = 0;
			}

			if (m === 0) {
				min = '00';
			}
			else {
				min = `${m}`
			}


			if (h < 10 ) {
				hour = `0${h}`;
			}
			else if (h > 12) {
				hour = `${h-12}`;
			}
			else {
				hour = `${h}`;
			}


			if (h < 11 || h === 24 ) {
				meridian = 'AM'
			}
			else {
				meridian = 'PM';
			}

			rideTimeItems.push( <MenuItem key={i} value={i} primaryText={ `${hour}:${min} ${meridian}`} /> );
		}


		return rideTimeItems;
	}



	handleSourceFocus(event) {
		// change the error status first
	    if (this.state.sourceStationError !== '') {
	    	
	    	this.setState({sourceStationError: ''});
	    }

	   	// // remove search city first
	   	// this.props.onSourceRemoveCity();
	}


	handleDestinationFocus(event) {
		// change the error status first
	    if (this.state.destinationStationError !== '') {
	    	
	    	this.setState({destinationStationError: ''});
	    }
	}




	handleSourceSearchQueryOnChange(value) {
	    // do something with query input
	   		 	    
	    // send search station query
	    this.props.onSearchRequest('source', value, this.props.city);

	    // update the source q
	    this.props.handleSourceQ(value);
	}





	handleDestinationSearchQueryOnChange(value) {
	    // do something with query input
	    
	    // send search station query
	    this.props.onSearchRequest('destination', value, this.props.city);

	    // update the source q
	    this.props.handleDestinationQ(value);
	}



	handleSourceStationOnChange(source_id)  {
	    // do something with final slected value (id)
	    
	    // update station ids
	    this.props.handleSourceStation(source_id);

	    // now update the city based on the current source station selection
	    // this.props.onUpdateCity(source_id);

	    // this.setState({source_station: source_id});
	    this.props.handleSourceQ(source_id);
	}


	handleDestinationStationOnChange(destination_id)  {
	    // do something with final slected value (id)
	    
	    // update station ids
	    this.props.handleDestinationStation(destination_id);

	    // this.setState({destination_station: destination_id});
	    this.props.handleDestinationQ(destination_id);
	}



	// handlePreferenceChange(event, index, value) {
	// 	event.preventDefault();

	// 	this.props.handlePreference(value);
	// }
	// 
	handlePreferenceChange(value) {
		// change the error status first
		if (this.state.preferenceError !== '') {
	    	
	    	this.setState({preferenceError: ''});
		}

		// this.setState({preference: value});
		this.props.handlePreference(value);
	}


	// handleRideTimeChange(event, index, value) {
	// 	event.preventDefault();

	// 	this.props.handleRideTime(value);
	// } 

	handleRideTimeChange(value) {
		// change the error status first if exists
		if (this.state.rideTimeError !== '') {
	    	
	    	this.setState({rideTimeError: ''});
		}

		// this.setState({rideTime: value});
		this.props.handleRideTime(value);
	} 



	submitRequest(event) {
		event.preventDefault();

		let err = false;
		let errors = {};

		// check for incomplete data
		if (this.props.source_id === null) {
			errors = {...errors, sourceStationError: 'Source Required'};
			err = true;
		}
		if (this.props.destination_id === null) {
			errors = {...errors, destinationStationError: 'Destination Required'};
			err = true;
		}
		if (this.props.preference === null) {
			errors = {...errors, preferenceError: 'Preference Required'};
			err = true;
		}
		if (this.props.rideTime === null) {
			errors = {...errors, rideTimeError: 'Ride Time Required'};
			err = true;
		}


		if (!err) {
			// only send request if there is not error 
			this.props.onOpruutRequest();
		}
		else {
			// let the system render the errors
			this.setState(errors);
		}
		
	}





	customPreferenceItem(item) {
	    
	    const containerStyle = {
	      display: 'flex',
	      flexDirection: 'row'
	    };

	    // const imageStyle = {
	    //   display: 'flex',
	    //   width: '32px',
	    //   height: '32px',
	    //   flexGrow: 0,
	    //   marginRight: '8px',
	    //   backgroundColor: '#ccc'
	    // };

	    const contentStyle = {
	      display: 'flex',
	      flexDirection: 'column',
	      flexGrow: 2
	    };

	    return (
	      <div style={containerStyle} >
	        {/* <img src={item.img} style={imageStyle} /> */}
	        <div style={contentStyle} >
	          <strong>{item.preference}</strong>
	          {/* <small>{item.meaning}</small>  */}
	        </div>
	      </div>
	    );
	}





	customRideTimeItem(item) {
	    
	    const containerStyle = {
	      display: 'flex',
	      flexDirection: 'row'
	    };

	    const contentStyle = {
	      display: 'flex',
	      flexDirection: 'column',
	      flexGrow: 2
	    };

	    return (
	      <div  style={containerStyle} >
	        <div style={contentStyle} >
	          {item.rideTime}
	        </div>
	      </div>
	    );
	}





	renderRequestBar() {


		return (
			<div>
				
				<p className={ classnames('lead', opruutRequestBarStyles.lead) } >Welcome to OpRuut.</p>
				<p className={ classnames('lead2', opruutRequestBarStyles.lead2) } >Hope you are doing great. Lets have some fun. Let's comfortize your travels. Let's find the most optimized route for your metro travels.</p>
				<div className={ classnames('row') } >
					<div className={ classnames('col-xs-6', 'col-md-6', 'input-elements', opruutRequestBarStyles.inputElements) } >
				
						<Autocomplete
				          	direction="down"
				          	label="From Station"
				          	multiple={ false }
				          	onChange={ this.handleSourceStationOnChange }
				          	onQueryChange={ this.handleSourceSearchQueryOnChange }
				          	onFocus={ this.handleSourceFocus }
				          	source={ this.props.station_list }
				          	value={ this.props.source_q }
				          	showSuggestionsWhenValueIsSet={ false }
				          	suggestionMatch='start'
				          	theme={ autocompleteTheme }
				          	error={ this.state.sourceStationError }
				        />
				    

					</div>
					<div className={ classnames('col-xs-6', 'col-md-6', 'input-elements', opruutRequestBarStyles.inputElements) } >
						
						<Autocomplete
				          	direction="down"
				          	label="To Station"
				          	multiple={ false }
				          	onChange={ this.handleDestinationStationOnChange }
				          	onQueryChange={ this.handleDestinationSearchQueryOnChange }
				          	onFocus={ this.handleDestinationFocus }
				          	source={ this.props.station_list }
				          	value={ this.props.destination_q }
				          	showSuggestionsWhenValueIsSet={ false }
				          	suggestionMatch='start'
				          	theme={ autocompleteTheme }
				          	error={ this.state.destinationStationError }
				        />

					</div>
				</div>
			

				<div className={ classnames('row') } >
					<div className={ classnames('col-xs-6', 'col-md-5', 'col-md-offset-1', 'input-elements', opruutRequestBarStyles.inputElements) } >
							
						{/* <SelectField
				          	floatingLabelText="Preference"
				          	floatingLabelFixed={false}
				          	value={ this.props.preference }
				          	onChange={ this.handlePreferenceChange } 
				          	fullWidth={ true } 
				        >
				          	
				          	<MenuItem key={0} value={0} primaryText="No Preference" />
				          	<MenuItem key={1} value={1} primaryText="Time" />
				          	<MenuItem key={2} value={2} primaryText="Comfort" />
				          	<MenuItem key={3} value={3} primaryText="Both" />
				        
				        </SelectField>
				    */}
				   
				   		<Dropdown
					        auto={false}
					        source={Preferences}
					        onChange={this.handlePreferenceChange}
					        label='Preference'
					        template={this.customPreferenceItem}
					        value={this.props.preference}
					        error={this.state.preferenceError}
					        theme={dropdownTheme}
					    />

					</div>

					<div className={ classnames('col-xs-6', 'col-md-5', 'input-elements', opruutRequestBarStyles.inputElements) } >
							
						{/* <SelectField
				          	floatingLabelText="Ride Time"
				          	floatingLabelFixed={false}
				          	value={ this.props.rideTime }
				          	onChange={ this.handleRideTimeChange } 
				          	fullWidth={ true }
				          	maxHeight={250}

				        >
				          	
				          	{ this.renderRideTimeItems() }
				        
				        </SelectField>

						*/}

				        <Dropdown
					        auto={false}
					        source={RideTimes}
					        onChange={this.handleRideTimeChange}
					        label='Ride Time'
					        template={this.customRideTimeItem}
					        value={this.props.rideTime}
					        error={this.state.rideTimeError}
					        theme={dropdownTheme}
					    />


					</div>
				</div>

				<div className={ classnames('row') } >
					<div className={ classnames('col-xs-4', 'col-xs-offset-4', 'input-elements', opruutRequestBarStyles.inputElements) } >
							
						<RaisedButton label="Submit" primary={true} style={styles.selectfield} onTouchTap={ this.submitRequest }  />

					</div>
				</div>

			</div>
		);
	}




	renderRequestLoading() {

		// this.setState({progressCompleted: false});

		return (
			 <div>
				
				{/* <RequestRouteLoading 
					source={ this.state.source_station }
					destination={ this.state.destination_station }
				/> */}

				{/*  <LinearProgress mode="determinate" value={this.state.completed}  style={styles.customColor} />  */}

				<Spinners type='chasing-dots' />

			</div>
		);
	}


	progress(completed) {
	    
	    if (this.props.isFetching) {
		    if (completed > 100) {
	    		this.setState({completed: 0});
	    		this.timer = setTimeout(() => { this.setState({completed: 0}); this.progress(5); }, 500);
		    
		    } 
		    else {
		      this.setState({completed});
		      let  diff = 20 +  Math.random() * 10;
		      this.timer = setTimeout(() => this.progress(completed + diff), 1000);
		    }
		}
		else {
			this.setState({completed: 100});
			this.clearTimeout(this.timer);
			this.progressCompletion = setTimeout(() => this.setState({progressCompleted: true}) , 500);
			
		}
	}



	setProgress() {
		if (!this.timer) {
	    	this.timer = setTimeout(() => this.progress(5), 1000);
		}
	}



	renderSpinners() {

		return (

			<div>
				
				
				{/* <Spinners type='wave' /> */}
				{/* <Spinners type='bubbles' /> */}
				{/* <Spinners type='wandering-cubes' /> */} 
				<Spinners type='chasing-dots' style={styles.customSpinners} />
				{/* <Spinners type='rotating-plane' /> */}
				{/* <Spinners type='loading-circles' />
				<Spinners type='loading-fading-circles' /> */}
				{/* <Spinners type='cube-grid' /> */} 
				{/* <Spinners type='cylon' />
				<Spinners type='cylon-svg' /> */}

				{/* <LinearProgress mode="indeterminate" style={styles.customColor} /> */}

				<br />
				<br />

				<LinearProgress mode="determinate" value={this.state.completed}  style={styles.customColor} /> 

			</div>	
		);
	}


	componentDidMount() {
	    // this.timer = setTimeout(() => this.progress(5), 1000);
	}




	render() {
		
		return (

			
			<div className={ classnames('jumbotron', opruutRequestBarStyles.jumbotron, opruutRequestBarStyles.homeWelcomeItem) } >
				
				 { 
				 	this.props.isFetching ? this.renderRequestLoading() : this.renderRequestBar()  
				 }

				{/*  { this.renderSpinners() } */}
													
			</div>

			

		);
	}
}




OpruutRequestBar.propTypes = {

	preference: React.PropTypes.number,
	rideTime: React.PropTypes.number,
	source_id: React.PropTypes.oneOfType([
	    React.PropTypes.oneOf([null]),
	    React.PropTypes.number,
	]),
	destination_id: React.PropTypes.oneOfType([
	    React.PropTypes.oneOf([null]),
	    React.PropTypes.number,
	]),
	city: React.PropTypes.string,
	isFetching: React.PropTypes.bool, 
	isIncompleteRequest: React.PropTypes.bool, 
	station_list: React.PropTypes.object,
	onSearchRequest: React.PropTypes.func,
	onOpruutRequest: React.PropTypes.func,
	handlePreference: React.PropTypes.func,
	handleRideTime: React.PropTypes.func,
	handleSourceStation: React.PropTypes.func,
	handleDestinationStation: React.PropTypes.func,
	onUpdateCity: React.PropTypes.func,
	onUpdateCityList: React.PropTypes.func,
	onSourceRemoveCity: React.PropTypes.func

};





export default OpruutRequestBar;

