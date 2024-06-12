import json
import requests

ENDPOINT = 'https://api.cron-job.org'

headers = {
    'Authorization': 'Bearer RtWJSAE3zWRkwYzX70uo4lfPzfJSjLr5r7udu8Poh2c=',
    'Content-Type': 'application/json'
}
payload = {
    'job': {
        'enabled': True
    }
}

result = requests.patch(ENDPOINT + '/jobs/4061357', headers=headers, data=json.dumps(payload))
print(result.json())