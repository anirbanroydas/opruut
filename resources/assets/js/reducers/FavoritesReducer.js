import { REQUEST_FAVORITES, RECEIVE_FAVORITES, RECEIVE_FAVORITES_FAILURE, UPDATE_LIMIT_FAVORITES, 
	UPDATE_CURSOR_FAVORITES, REMOVE_INVALID_DATA, TOGGLE_FAVORITE_STATUS, ADD_OPRUUT_TO_FAVORITES } from '../actions';


const initialState =  {
	data: [],
	isFetching: false,
	lastUpdated: null,
	error: null,
	firstTimeFavorites: true,
	cursor: null,
	limit: null
};




export default function opruutList(state = initialState, action) {
	
	switch (action.type) {

	case UPDATE_CURSOR_FAVORITES:
		// console.log('[UPDATE_CURSOR_FAVORITES] - action : ', action);
      	// console.log('[UPDATE_CURSOR_FAVORITES] - state : ', state);
		return {
			...state, 
			cursor: action.cursor
		};


	case UPDATE_LIMIT_FAVORITES:
		// console.log('[UPDATE_LIMIT_FAVORITES] - action : ', action);
      	// console.log('[UPDATE_LIMIT_FAVORITES] - state : ', state);
		return {
			...state, 
			limit: action.limit
		};

	

	case REQUEST_FAVORITES:
		// console.log('[REQUEST_FAVORITES] - action : ', action);
      	// console.log('[REQUEST_FAVORITES] - state : ', state);
		return {
			...state, 
			isFetching: true
		};



	case ADD_OPRUUT_TO_FAVORITES:
		return {
			...state, 
			data: [action.data, ...state.data],
			lastUpdated: Date.now()
		};


	case RECEIVE_FAVORITES:
		// console.log('[RECEIVE_FAVORITES] - action : ', action);
      	// console.log('[RECEIVE_FAVORITES] - state : ', state);
		return {
			...state, 
			isFetching: false,
			data: [...state.data, ...action.data],
			lastUpdated: Date.now(),
			error: null
		};


	case RECEIVE_FAVORITES_FAILURE:
		// console.log('[RECEIVE_FAVORITES_FAILURE] - action : ', action);
      	// console.log('[RECEIVE_FAVORITES_FAILURE] - state : ', state);
      	return {
      		...state,
  			isFetching: false,
  			error: action.error
      	};



    case REMOVE_INVALID_DATA:
    	// console.log('[REMOVE_INVALID_DATA] - action : ', action); 	
    	let newFavoritesData = [...state.data];

    	for (let position in action.invalids.sort().reverse()) {
			// remove the data
			newFavoritesData.splice(position, 1);
    	}

    	if (action.invalids.length > 0) {
    		return  {
				...state, 
				data: newFavoritesData,
				lastUpdated: Date.now()
			};

    	}
    	else {
    		// since no invalid data 
    		// hence return the state as it is
    		return state;
    	}





    case TOGGLE_FAVORITE_STATUS:
    	// console.log('[TOGGLE_FAVORITE_STATUS] - action : ', action);
    	let newFavoriteStatusData = [...state.data];
    	let favoritesStatusIndex = 0;
    	let toggled = false;
    	let newOpruut;

    	for (let opruut of newFavoriteStatusData) {
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - opruut : ', opruut);
    		if (opruut.id === action.opruutId) {
    			toggled = true;
    			let isFavorited = opruut.isFavorited;
				
				newOpruut = {
					...opruut, 
					isFavorited: !opruut.isFavorited, 
					favorites_count: isFavorited ? --opruut.favorites_count : ++opruut.favorites_count
				};
    			
    			break;
    		}
    		
    		++favoritesStatusIndex;
    	}

    	if (toggled) {
    		// means some newData has been generatoed
    		newFavoriteStatusData.splice(favoritesStatusIndex, 1, newOpruut);
			return  {
				...state, 
				data: newFavoriteStatusData,
				lastUpdated: Date.now()
			};
    	}
    	else {
    		// jsut pass original state with any change
    		return state;
    	}
  	

    	

	default:
		// console.log('[DFAULT] - action : ', action);
      	// console.log('[DFAULT] - state : ', state);
		return state;
			
	}

}