eZ Publsih Event is an event datatype

You can use it for one day or multi-day events.

An event will be saved in eZ as one object and one doc per day in SOLR.

Installation
===
You have to add a fieldtype (name e.g ezpevent) to your event class. Here you can set "from to"-dates wich are included and also excluded.

If you don't set time the time will be saved as 00:00.

Limitaions
===

* If an object state is changed, the state change has no effect on the event core.
