import { REQUEST_OPRUUTS, UPDATE_LIMIT_OPRUUT_LIST, UPDATE_CURSOR_OPRUUT_LIST, 
	RECEIVE_OPRUUTS, RECEIVE_OPRUUTS_FAILURE, ADD_OPRUUT_TO_LIST,
	TOGGLE_FAVORITE_STATUS_FROM_HOME} from '../actions';


const initialState =  {
	data: [],
	isFetching: false,
	lastUpdated: null,
	error: null,
	cursor: null,
	limit: null
};


export default function opruutList(state = initialState, action) {
	
	switch (action.type) {

	case UPDATE_CURSOR_OPRUUT_LIST:
		return {
			...state, 
			cursor: action.cursor
		};


	case UPDATE_LIMIT_OPRUUT_LIST:
		return {
			...state, 
			limit: action.limit
		};

	

	case REQUEST_OPRUUTS:
		return {
			...state, 
			isFetching: true
		};


	case RECEIVE_OPRUUTS:
		return {
			...state, 
			isFetching: false,
			data: [...state.data, ...action.data],
			lastUpdated: Date.now(),
			error: null
		};


	case ADD_OPRUUT_TO_LIST:
		return {
			...state, 
			data: [action.data, ...state.data],
			lastUpdated: Date.now()
		};

	case RECEIVE_OPRUUTS_FAILURE:
      
      	return {
      		...state,
  			isFetching: false,
  			error: action.error
      	};


    case TOGGLE_FAVORITE_STATUS_FROM_HOME:
    	// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - action : ', action);
    	let newFavoriteStatusData = [...state.data];
    	let favoritesStatusIndex = 0;
    	let toggled = false;
    	let newOpruut;

    	for (let opruut of newFavoriteStatusData) {
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - opruut : ', opruut);
    		if (opruut.id === action.opruutId) {
    			// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - opruut.id === action.opruutId ');
    			toggled = true;
    			let isFavorited = opruut.isFavorited;
    			// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - original isFavorited : ', isFavorited);
    			// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - original favorites_count : ', opruut.favorites_count);
    			// let favorites_count = isFavorited ? --opruut.favorites_count : ++opruut.favorites_count;
    			//console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - new favorites_count : ', favorites_count);
				// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - original opruut : ', opruut);
				newOpruut = {
					...opruut, 
					isFavorited: !opruut.isFavorited, 
					favorites_count: isFavorited ? --opruut.favorites_count : ++opruut.favorites_count
				};
    			// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - newOpruut : ', newOpruut);
    			break;
    		}
    		
    		favoritesStatusIndex++;
    	}

    	if (toggled) {
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - toggled is true : ', toggled);
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - present newFavoriteStatusData : ', newFavoriteStatusData);
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - present favoritesStatusIndex : ', favoritesStatusIndex);
    		newFavoriteStatusData.splice(favoritesStatusIndex, 1, newOpruut);
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - new newFavoriteStatusData : ', newFavoriteStatusData);
    		// means some newData has been generatoed
			let newState =   {
				...state, 
				data: newFavoriteStatusData,
				lastUpdated: Date.now()
			};

			// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - newState : ', newState);
			return newState;
    	}
    	else {
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - toggled is false : ', toggled);
    		// console.log('[TOGGLE_FAVORITE_STATUS_FROM_HOME] - newState : ', state);
    		// jsut pass original state with any change
    		return state;
    	}


   //  	if (action.opruutId in state.data) {
   //  		// mean the corresponding opruut is present in the data
   //  		toggled = true;
   //  		let isFavorited = state.data[action.opruutId].isFavorited;

   //  		return  {
			// 	...state, 
			// 	data: [
			// 		...state.data, 
			// 		[action.opruutId]: {
			// 			...state.data[action.opruutId], 
			// 			isFavorited: !state.data[action.opruutId].isFavorited, 
			// 			favorites_count: isFavorited ? --state.data[action.opruutId].favorites_count : ++state.data[action.opruutId].favorites_count
			// 		] 
			// 	],
			// 	lastUpdated: Date.now()
			// };

   //  	}
   //  	else {
   //  		// since data opruut with opruutid not present
   //  		// hence returning the stae as it is
   //  		return state;
   //  	}


	default:
		return state;
			
	}

}