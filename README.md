# outbox-pattern

This is an example application using Transactional Outbox Pattern

Application assumptions:
- MongoDb
- Doctrine Mongo ODM
- Command Bus
- No framework
- PHP League components
- Symfony CLI Command component

The change flow:
1. The command is executed on command bus
2. The command is sent to the handler
3. The handler do operations on the domain object
4. The domain object changes state and produces events
5. The events are dispatched
6. The events are intercepted
7. The domain object is persisted via Doctrine ODM
8. The Doctrine dispatches pre persist event
9. The pre persist event is used to add events to the persisted object
10. Finally, after the command handler the middleware calls Doctrine flush that stored the data to the database
