1. Use docker:
   - One of the skill we need for new developers is to be familiar with containerization.
   - I have created docker-compose.yml file to make it easier to run the application using docker.
   - The docker-compose create two containers, one for the application and one for swagger UI.
2. Change to different API provider.
    - The current API provider (ZenQuotes) has no free tier and requires payment information to use it.
    - I have changed the API provider to api-ninjas which has a free tier and does not require payment information.
    - Another provider is Quotable.io which also has a free tier and does not require payment information.
3. The file app/Http/Requests/FavoriteDeleteRequest.php has no much differences with the implemented version
4. The file app/Http/Requests/FavoriteStoreRequest.php has no much differences with the implemented version