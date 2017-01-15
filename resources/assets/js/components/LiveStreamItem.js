import React from 'react';
import classnames from 'classnames';

import Avatar from 'material-ui/Avatar';
import livestreamItemStyles from '../../sass/components/livestreamItem.scss';
import timesStyles from '../../sass/components/times.scss';



class LiveStreamItem extends React.Component {
	
	constructor(props) {
	  super(props);
	
	  this.getPreference = this.getPreference.bind(this);
	}



	getPreference() {
	 	switch (this.props.preference) {
	 		case 0:
	 			return ' No Preference ';
	 		case 1:
	 			return ' Time ';
	 		case 2:
	 			return ' Comfort ';
	 		case 3:
	 			return ' Both ';
	 	}
	}



	render() {
		return (

			<li className={ classnames('notification') } >
			    <div className={ classnames('media') } >
			        <div className={ classnames('media-left media-middle') } >
			        	<div className={ classnames('media-object') } >
				        	<Avatar 
								src={ this.props.avatar ? this.props.avatar  : "media/avatars/avatar_male.jpg" }
								size={32} 
								className={ classnames(livestreamItemStyles.cocktailNotificationLeftImg) }  
							/>
			          	</div>
			        </div>
			        <div className={ classnames('media-body') } >
			          	<div className={ classnames('meadia-heading', livestreamItemStyles.cocktailNotificationTitle) } >
			          		<a href="#" className={ classnames(livestreamItemStyles.cocktailNotificationResourceLink) } >
			          			{this.props.userName ? `${this.props.userName} ` : `Anonymous `}
			          		</a> { this.props.type === 'favorites' ? 'liked the request' : 'requested an opruut from' }
			          		<a href="#" className={ classnames(livestreamItemStyles.cocktailNotificationResourceLink) } >
			          			{ ` ${this.props.from} ` }
			          		</a> to 
			          		<a href="#" className={ classnames(livestreamItemStyles.cocktailNotificationResourceLink) } >
			          			{ ` ${this.props.to} ` }
			          		</a> with preference as 
			          		<a href="#" className={ classnames(livestreamItemStyles.cocktailNotificationResourceLink) } >
			          			{this.getPreference() }
			          		</a> and ride time of
			          		<a href="#" className={ classnames(livestreamItemStyles.cocktailNotificationResourceLink) } >
			          			{` ${this.props.ride_time_tz} ` }
			          		</a>
			          	</div>

			          	<div className={ classnames('notification-footer') } >
			            	<time 
			            		className={ classnames(livestreamItemStyles.cocktailNotificationResourceLinkTime, timesStyles.timestamp, timesStyles.secondsTimestamp) }  
			            		data-utc-timestamp="1479759590448" 
			            		data-locale-date="22 Nov" 
			            		data-locale-time="1:50 am" 
			            	>
			            		{this.props.created_at_humans}
			            	</time>
			          	</div>

			        </div>
			    </div>
			</li>

		);
	}
}


LiveStreamItem.propTypes = {

	streamId: React.PropTypes.number,
	userName: React.PropTypes.string,
	userUsername: React.PropTypes.string,
	avatar: React.PropTypes.string,
	from: React.PropTypes.string,
	to: React.PropTypes.string,
	preference: React.PropTypes.number,
	ride_time_tz: React.PropTypes.string,
	created_at_humans: React.PropTypes.string,
	type: React.PropTypes.string,

};


export default LiveStreamItem;

