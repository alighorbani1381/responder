
<br>

### 🚀 Generate API Response as fast as light!
We solve some problem for You with a package You can call Responder 

actually developers make a specific structure for api that developed
 
###  ✅️ Advantages
 <ol>
<li>You don't need to set pagination logic in your resource because inject automatically</li>
<li>You can make a different response with elegant syntax (use facade)</li>
<li>Automatic Message Mapper on response for example(title => users-list)</li>
<li>Prevent Human mistakes when generate response structure manually</li>
<li>Unlimited Define Structure and used in project</li>
<li>If you decide to Change Your you can do this with little changed</li>
</ol> 

### 👨‍💻 Usage 

In this Example use the resource that contains several items (such as users list)

```php
<?php

use Jenssegers\Mongodb\Eloquent\Model;
use Alighorbani\Responder\ResponderFacade;

class UserController extends Model
{
    public function getUsersList()
    {
        $users = User::all();
        
        return ResponderFacade::resourceResponse($users, 'USERS.LIST', UserResource::class);
    }
}
```

```json
{
    "success": true,
    "title": "The List of Users Resource",
    "result": [
        {
            "id": 1,
            "name" : "Ali",
            "lastname" : "Ghorbani",
            "birthday" : "2003-01-01"
        },  
        {
            "id": 2,
            "name" : "Mohammad",
            "lastname" : "Karimi",
            "birthday" : "2003-02-18"
        }
    ]
}
```

# TODO

- [x] Installing With Composer Package & Dependencis
- [ ] how to use the make responder
- [ ] how to use the message config 
- [ ] how to use the macroable on this package (to add functionality) 
