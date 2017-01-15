import React from 'react';
import classnames from 'classnames';

import LiveStreamItem from './LiveStreamItem';

import livestreamListStyles from '../../sass/components/livestreamList.scss';


class LiveStreamList extends React.Component {
	
	livestreamItems() {

		if (this.props.streams.length === 0) {

			return (

				<li >
		      		<div className={ classnames(livestreamListStyles.cocktailEmptyNotification) } >
				      	<div className={ classnames(livestreamListStyles.cocktailEmptyNotificationContent) } >
				        	Yikes! No Data to Show as of now. <br /><br /><br />
				       	 	Come back later and we will surely have something for you.
				      	</div>
				    </div>
				</li>

			);
		}

		let livestreams =  this.props.streams.map((stream) => {
		
			return (
				<LiveStreamItem 
					key={stream.id} 
					streamId={stream.id}
				    userName={stream.userName} 
				    userId={stream.userId}
				    userUsername={stream.userUsername}
				    avatar={stream.userAvatar}
				    from={stream.source}
				    to={stream.destination}
				    preference={stream.preference}
				    ride_time_tz={stream.ride_time_tz}
					created_at_humans={stream.created_at_humans}
					type={stream.type}
				/>
			);
		
		});

		return livestreams;
	
	}

	


	render() {

		return (
						
			<div className={ classnames('dropdown-notifications', livestreamListStyles.cocktailStreamWrapper) } >
								  	
			  	<ul className={ classnames('dropdown-toolbar', livestreamListStyles.cocktailStreamHeader) } >
		      		{/* <div className={ classnames('dropdown-toolbar-actions') } >
	                  	<img className={ classnames('img-responsive',  livestreamListStyles.cocktailStreamImg) }  src="media/icons/drink-4.png" alt="menu_icon_image" />
	                </div>
	            */}

	                <h3 className={ classnames('dropdown-toolbar-title', livestreamListStyles.cocktailStreamHeading) } >Live Request Stream</h3>
		    	</ul>						

				<ul className={ classnames(livestreamListStyles.cocktailStreamBody, 'notifications') } >
											
					{ this.livestreamItems() }

				</ul>

			</div>
		
		);
	}

};


LiveStreamList.propTypes = {

	streams: React.PropTypes.array,
	isFetching: React.PropTypes.bool,
	scrollFunc: React.PropTypes.func

};


export default LiveStreamList;

