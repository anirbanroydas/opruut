import axios from 'axios';
import { browserHistory } from 'react-router';

import { removeSourceId, removeDestinationId } from './OpruutRequestActions'


export const RECEIVE_STATION_LIST = 'RECEIVE_STATION_LIST';
export const RECEIVE_STATION_LIST_FAILURE = 'RECEIVE_STATION_LIST_FAILURE';
export const UPDATE_SEARCH_CITY = 'UPDATE_SEARCH_CITY';
export const UPDATE_SOURCE_Q = 'UPDATE_SOURCE_Q';
export const UPDATE_DESTINATION_Q = 'UPDATE_DESTINATION_Q';
export const ADD_QUERY_TO_SEARCH_INDEX = 'ADD_QUERY_TO_SEARCH_INDEX';
export const REMOVE_SEARCH_CITY = 'REMOVE_SEARCH_CITY';




function updateCity(city) {
	// console.log('Action: SearchActions : updateCity, arguement : (city) ', city);

	return {
		type: UPDATE_SEARCH_CITY,
		city
	};
}






export function updateSearchCity(station_id) {
	// console.log('Action: SearchActions: type: dispatch : outside: updateSearchCity, arguement : (station_id) : and typeof(station_id)', station_id, typeof station_id);

	return function(dispatch, getState) {
		// console.log('Action: SearchActions : type: dispatch : inside: updateSearchCity, arguement : (getState) ', getState());

		let { search } = getState();
		let { station_list } = search;
		let city = null;
		
		// console.log('station_id In station_list');
		let station = station_list[station_id]
		city = station.split(',');
		city = city[1].split('|');
		city = city[0].trim().toLowerCase();
		city = city.replace(/\s+/g, '_');


		// console.log('city : ', city);
		// console.log('dispatch update city');
		dispatch(updateCity(city));

	};
	
}





export function removeSearchCity() {
	// console.log('Action: SearchActions: : removeSearchCity, arguement : () : ');

	return {
		type: REMOVE_SEARCH_CITY
	};

	
}






export function updateSourceQ(q) {

	return {
		type: UPDATE_SOURCE_Q,
		q
	}
}



export function updateDestinationQ(q) {
	
	return {
		type: UPDATE_DESTINATION_Q,
		q
	}
}




function receiveStationList(station_list) {
	// console.log('Action: SearchActions : receiveStationList, arguement : (station_list) : ', station_list);

	return {
		type: RECEIVE_STATION_LIST,
		station_list: station_list
	}
}


function receiveStationListFailure(err) {
	// console.log('Action: SearchActions : receiveStationListFailure, arguement : (err) : ', err);

	return {
		type: RECEIVE_STATION_LIST_FAILURE,
		error: err
	}
}




function addQueryToSearchIndex(q) {
	// console.log('Action: SearchActions : addQueryToSearchIndex, arguement : (q) : ', q);

	return {
		type: ADD_QUERY_TO_SEARCH_INDEX,
		q
	}
}




function fetchStations(q, city) {
	// console.log('Action: SearchActions : type: dispatch : outside:  fetchStations, arguement : (q, city) : ', q, city);
	
	return function(dispatch) {
		// console.log('Action: SearchActions : type: dispatch : inside:  fetchStations, arguement : dispatch: ');

		let search_url =  (city.length > 0) ? `/api/v1/search/stations?q=${q}&city=${city}` : `/api/v1/search/stations?q=${q}`;
		
		// Now hardcoding search url with city as Delhi
		// let search_url =  `/search/station?q=${q}&city=delhi`;

		axios.post(search_url)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    // process respnose to make a proper station list format
		    // merger with old list or craete new list or limt size of list 
		    // until whcih merger and then recreat

		    let station_list = response.data.station_list;
		    // console.log('station_list_array : ', station_list);
		    
		    // let station_list = {};

		    // for (let station of station_list_array) {
		    // 	// console.log('station_list : ', station_list);
		    // 	// console.log('station : ', station);
		    // 	station_list = {...station_list, ...station};
		    // }

		    // // console.log('Final_station_list : ', station_list);

		    // parse the station list to fit the search state	

		    // add q to serchIndexes
		    dispatch(addQueryToSearchIndex(q));	 
		     		    
		    return dispatch(receiveStationList(station_list));
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			return dispatch(receiveStationListFailure(error));
		});
	
	};

}





function shouldFetchStationList(searchIndexes, q) {
	// console.log('Action: SearchActions : shouldFetchStationList, arguement : (station_list, q}) : ', searchIndexes, q);

	if (!q || q === '') {
		return false;
	}
		
	// check if the search already been done using q
	if (searchIndexes.includes(q)) {
		// if search already been done with q, lets not redo the search and use from the local list
		return false;
	}

	return true;
}






export function fetchStationListIfNeeded(type, q, city) {
	// console.log('Action: SearchActions : type: dispatch: outside: fetchStationListIfNeeded, arguement : (q, city) : typeof(arguments) ', q, city, typeof q, typeof city);
	
	return function(dispatch, getState) {
		// console.log('Action: SearchActions : type: dispatch: inside: fetchStationListIfNeeded, arguement : (getState) : ', getState());

		let { search, opruutRequest } = getState();

		// update the source_id or destination id to null according to type if its not null
		if (type === 'source') {
			if (opruutRequest.source_id !== null) {
				dispatch(removeSourceId());
			}

			// if (search.city.length > 0) {
			// 	// change city to ''
			// 	dispatch(removeSearchCity());
			// }
		}
		else {
			if (opruutRequest.destination_id !== null) {
				dispatch(removeDestinationId());
			}
		}

		q = q.toLowerCase();

		if (shouldFetchStationList(search.searchIndexes, q)) {
		    
		    return dispatch(fetchStations(q, city))
		}
	};
}