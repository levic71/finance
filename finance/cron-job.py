import json
import requests

ENDPOINT = 'https://api.cron-job.org'

headers = {
    'Authorization': 'Bearer 5Wm1mRRadgEz4kBH+kitqXcjoPqCzodIpJL0ZyGX170=',
    'Content-Type': 'application/json'
}
payload = {
    'job': {
        'enabled': False
    }
}

result = requests.patch(ENDPOINT + '/jobs/4061357', headers=headers, data=json.dumps(payload))
print(result.json())