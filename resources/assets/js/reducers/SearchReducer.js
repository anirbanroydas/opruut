import { RECEIVE_STATION_LIST, RECEIVE_STATION_LIST_FAILURE, UPDATE_SEARCH_CITY, REMOVE_SEARCH_CITY, 
	ADD_QUERY_TO_SEARCH_INDEX, UPDATE_SOURCE_Q, UPDATE_DESTINATION_Q } from '../actions';


const default_station_list = {	
	// 7: 'Spain, Delhi', 14: 'England, Delhi', 23:'USA, Delhi', 43:'Thailand, Delhi', 343:'Tongo, Delhi', 65:'Slovenia, Delhi', 
	// 234:'Afghanistan, Delhi',	589:'Kabul, Delhi', 34:'Pashto, Delhi', 45:'Albania, Delhi',	10:'Tirana, Delhi',	2:'Shqipëria, Delhi',	
	// 12:'Algeria, Delhi',	112:'Algiers, Delhi',	3:'Dzayer, Delhi',13:'Berber, Delhi', 130:'American, Delhi', 15:'Samoa, Delhi',	98:'Pago, Delhi',
	// 150:'English, Delhi', 16:'Andorra, Delhi',	160:'Andorra, Delhi',  8:'Vella, Delhi',	18:'Andorra, Delhi', 180:'Catalan, Delhi',
	// 1234:'Angola, Delhi',	2341:'Luanda, Delhi',	3421:'Portuguese, Delhi', 1432:'Anguilla, Delhi', 1452:'The Valley, Delhi',	3562:'Anguilla, Delhi',	
	// 111:'Barbuda, Delhi',	222:'Saint John\'s, Delhi',	333:'Antigua, Delhi', 444:'Barbuda, Delhi',	555:'Argentina, Delhi',	666:'Buenos, Delhi',
	// 777:'Aires, Delhi', 888:'Spanish, Delhi', 33433:'Armenia, Delhi',	999:'Yerevan, Delhi',	101:'Hayastán, Delhi', 105:'Yerevan, Delhi',
	// 108:'Oranjestad, Delhi',	109:'Aruba, Delhi', 156:'Oranjestad, Delhi', 1024:'Dutch, Delhi',	
}




const initialState =  {
	station_list: default_station_list,
	searchIndexes: [],
	source_q: '',
	destination_q: '',
	type: 'station',
	city: '',
	error: null
};



export default function search(state = initialState, action) {
	
	switch (action.type) {
	
	case UPDATE_SEARCH_CITY:
		return {
			...state, 
			city: action.city
		};


	case REMOVE_SEARCH_CITY:
		return {
			...state, 
			city: ''
		};



	case UPDATE_SOURCE_Q:
		return {
			...state, 
			source_q: action.q
		};



	case UPDATE_DESTINATION_Q:
		return {
			...state, 
			destination_q: action.q
		};



	case RECEIVE_STATION_LIST:
		return {
			...state, 
			station_list: { ...state.station_list, ...action.station_list }
		};


	case RECEIVE_STATION_LIST_FAILURE:
		return {
			...state, 
			error: action.error
		};


	case ADD_QUERY_TO_SEARCH_INDEX:
		return {
			...state,
			searchIndexes: [...state.searchIndexes, action.q]
		}

	default:
		return state;
			
	}

}