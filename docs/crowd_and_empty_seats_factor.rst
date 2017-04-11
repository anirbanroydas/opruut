Crowd Factor
=============

Now since we looked into **IF**, lets focus on **crowd factor** and **empty seat factor**.

Crowd factor is directly proportional to **IF**. So we have to determine the **crowd factor** at a specific time by calculating the **IF** at the same time. We already saw how IF is calculated at a specific time usign the graphs and values.


Empty Seat Factor
==================

Now empty seats meand more probability to sit and hence means more comfort. Empty seat values will keep changing at every station and it also depends on the time of travel and also on the route. The station number in a particular metro line also determines the empty seat factor. 

Lets see a sample empty seat graph for a particular route and how different stations in that metro line may have different empty seats factor depending on time and station number.

.. image:: ../screenshots/empty_plot_blue-2_2_yamunaBank.png

So empty seat factory is determined using both time of travel and the above graph.

**Empty Seats = Total Seats(constant) - Crowd in train** [at a particular time].

**Comfort** = Sum of empty seats from source to destination (Since, comfort due to empty seats may change depending on the travel time because shot travel time does not require much comfort requirement, shorter travel time is mroe preferrable but in case of longer travel time, comfort become becomes important factor), i.e. for longer travel time, a large number of (-ve) seat comfort values adds upto a greater negative number which mean more discomfort compared to shorter travel time with lesser (-ve) empty seat vlaue.

**NOTE :**  If seat comfort starts with positive (+ve) value, then it remains constant for the rest of the journey.


Interchanges Factor
====================

This is pretty straight forward too. Its the number of interchanges that happen in a route from source to destination. Its is multiplied by an factor to normalize the value to affect the final calculation of **CF** with no bias.

So finally the **Confort Factor (CF)** can be determined by using the above two.

**CF = maximize(empty seat comfort factor) + minimize(crowd factor) + minimize(no. of interchanges of metro line junctions)**
