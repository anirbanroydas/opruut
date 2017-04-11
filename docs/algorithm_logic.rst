Algorithm & Logic
==================

We are going to consider (a). **shortest distance or shortest time** to reach destination and (b). **comfort factor** to reach destination. We will determine the optimized route based on these two.


Shortest Distance/Time
-----------------------

To determeine shortest route between source and destination, its pretty straight forward. We just have to find out shortest distance between two vertices in a graph. Here, our station network can be represented in a graph, hence we use **Neo4j** as the graph database to store and query the graph network of the metro stations.

Finding shortest distance is easy. Algorithms like **Shortest Distance** can help us deteremine it. Although its not very scalable but given there will be not huge number of stations in a city, those algorithms will do a good enough job.

But here, we are trying to find the optimized route not only on the basis of distance or time but also on the basis of other factors like comfort, crowd, finding a seat, time of travel.

**Few Things to Note**

* Speed of train is constant
* Means, shortest distance means shortest time too.


Comfort Factor
---------------

This one is not so straight forward. To determine comfort factor and weigh them against shortest distanct/time factor and optimize route using these we have to actually take into consideration how to determine the comfort factor.

So to determine comfort factor, focus is done on 3 factors, one is **crowd factor** for the route, **empty seat factor** throughout the route and **Interchanges** between different metro lines (eg: from blue line to yellow line then yellow line to red line, etc)

To determind **crowd factor** and **empty seat factory**, lets first look at **IF (Importance Factor)** and how it helps in determing the previous two and hence the **Comfort Factor**

