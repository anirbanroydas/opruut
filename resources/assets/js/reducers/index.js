import { combineReducers } from 'redux';
import { reducer as FormReducer } from 'redux-form';

import AuthReducer from './AuthReducer';
import OpruutRequestReducer from './OpruutRequestReducer';
import OpruutListReducer from './OpruutListReducer';
import SearchReducer from './SearchReducer';
import FavoritesReducer from './FavoritesReducer';
import LivestreamReducer from './LivestreamReducer';
import BrowserHistoryReducer from './BrowserHistoryReducer';


const rootReducer = combineReducers({
	
	form: FormReducer,
	opruutRequest: OpruutRequestReducer,
	opruuts: OpruutListReducer,
	search: SearchReducer,
	favorites: FavoritesReducer,
	livestream: LivestreamReducer,
	auth: AuthReducer,
	browserHistory: BrowserHistoryReducer
	
});


export default rootReducer;