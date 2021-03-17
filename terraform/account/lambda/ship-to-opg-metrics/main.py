import os
import requests
from requests_aws4auth import AWS4Auth

def handler(event, context):
    for message in event['Records']:
      call_api_gateway(str(message))

def call_api_gateway(json_data):
    url = os.getenv('OPG_METRICS_URL')
    api_key = os.getenv('API_KEY')
    method = 'PUT'
    path = '/development/metrics'
    headers = {
      'Content-Type': 'application/json',
      'Content-Length': str(len(str(json_data))),
      'x-api-key': api_key
      }

    response = requests.request(
        method=method,
        url=url+path,
        json=json_data,
        headers=headers
    )
    print(response.text)
