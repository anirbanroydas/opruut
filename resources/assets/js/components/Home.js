import React from 'react';
import { browserHistory } from 'react-router';
import classnames from 'classnames';

import OpruutRequestBar from './OpruutRequestBar';
import ProfileBar from './ProfileBar';
import OpruutList from './OpruutList';
import LiveStreamList from './LiveStreamList';

import homeStyles from '../../sass/components/home.scss';




class Home extends React.Component {

	componentWillMount() {
		// console.log('HOme Component Will MOunt');
		// console.log('browser history : ', browserHistory.getCurrentLocation());

		this.props.actions.updateNavbarQuickLink();
		
		// fetch initial data 
		this.props.actions.fetchLivestreamIfNeeded();
		this.props.actions.subscribeToLivestreamIfNeeded();	

	}

	componentDidMount() {
		// console.log('HOme Component Did MOunt');
		
		this.props.actions.fetchOpruutListFirstTimeIfNeeded();
	}




	render() {
		
		return (       
            			
			<div className={ classnames('container', homeStyles.homeContainer) } >
				<div className={ classnames('row') } >							
					
					<div className={ classnames('col-md-2', 'hidden-xs', 'hidden-sm', homeStyles.leftContentWrapper) } >

						<ProfileBar  
							authenticated={ this.props.authenticated } 
							userinfo={ this.props.userinfo }
							globalRequests={ this.props.globalRequests }
						/>

					</div>

					<div className={ classnames('col-xs-12', 'col-md-7', homeStyles.centerContentWrapper) } >
						<div className={ classnames('center-block', homeStyles.opruutRequestBarContainer) } >									
							
							<OpruutRequestBar 
								onSearchRequest={ (type, q, city) => this.props.actions.fetchStationListIfNeeded(type, q, city) }
								onUpdateCity={ (station_id) => this.props.actions.updateSearchCity(station_id) }
								onSourceRemoveCity = { () => this.props.actions.removeSearchCity() }
								onOpruutRequest={ (requestData) => this.props.actions.fetchOpruutIfNeeded(requestData) }
								handleRideTime={ (rideTime) => this.props.actions.updateRideTime(rideTime) } 
								handlePreference={ (preference) => this.props.actions.updatePreference(preference) }
								handleSourceStation={ (source_id) => this.props.actions.updateSourceStation(source_id) } 
								handleDestinationStation={ (destination_id) => this.props.actions.updateDestinationStation(destination_id) }
								handleSourceQ={ (q) => this.props.actions.updateSourceQ(q) } 
								handleDestinationQ={ (q) => this.props.actions.updateDestinationQ(q) }
								preference={ this.props.opruutRequest.preference }
								rideTime={ this.props.opruutRequest.rideTime }
								source_id={ this.props.opruutRequest.source_id }
								destination_id={ this.props.opruutRequest.destination_id }
								station_list={ this.props.search.station_list }
								city={ this.props.search.city }
								source_q = { this.props.search.source_q }
								destination_q = { this.props.search.destination_q }
								isFetching={ this.props.opruutRequest.isFetching }
								isIncompleteRequest={ this.props.opruutRequest.isIncompleteRequest }
							/>

						</div>

						<div className={ classnames('center-block', homeStyles.opruutRequestListContainer) } >	

							<OpruutList 
								opruuts={ this.props.opruuts.data }  
								isFetching={ this.props.opruuts.isFetching }
								toggleFavorite={ (selectedOpruut, isFavorited) => this.props.actions.toggleFavoriteFromHome(selectedOpruut, isFavorited) }
								authenticated={ this.props.authenticated }
								scrollFunc={ (requestData, fetch_type) => this.props.actions.fetchOpruutListIfNeeded(requestData, fetch_type) }
							/>

							
						
						</div>
													
					</div>

					<div className={ classnames('col-md-3', 'hidden-xs', 'hidden-sm', homeStyles.rightContentWrapper) } >

						<LiveStreamList 
							streams={ this.props.livestream.data }
							isFetching={ this.props.livestream.isFetching }
							scrollFunc={ () => this.props.actions.fetchLivestreamIfNeeded() }
						/>
						
					</div>
					
				</div>
			</div>
			
		);
	}

}






Home.propTypes = {
	
	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.oneOfType([
	    React.PropTypes.oneOf([null]),
	    React.PropTypes.object
	]),
	actions: React.PropTypes.object,
	opruuts: React.PropTypes.object,
	globalRequests: React.PropTypes.number,
	opruutRequest: React.PropTypes.object,
	search: React.PropTypes.object,
	livestream: React.PropTypes.object,
};





export default Home;





