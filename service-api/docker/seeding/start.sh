#!/bin/sh

echo Starting seeding

if ! [[ -z "${AWS_ENDPOINT_DYNAMODB}" ]]; then

    # If we're not running against AWS' endpoint

    DYNAMODN_ENDPOINT=http://${AWS_ENDPOINT_DYNAMODB}

    echo Using local DynamoDB
    /usr/local/bin/waitforit -address=tcp://${AWS_ENDPOINT_DYNAMODB} -timeout 60 -retry 6000 -debug

    # ----------------------------------------------------------
    # Add any setup here that is performed with Terraform in AWS.

    # Temporary table.
    aws dynamodb create-table \
    --attribute-definitions AttributeName=ActorCode,AttributeType=S \
    --table-name ActorCodes \
    --key-schema AttributeName=ActorCode,KeyType=HASH \
    --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10 \
    --region eu-west-1 \
    --endpoint $DYNAMODN_ENDPOINT

    aws dynamodb create-table \
    --attribute-definitions AttributeName=Id,AttributeType=S AttributeName=Email,AttributeType=S AttributeName=ActivationToken,AttributeType=S AttributeName=PasswordResetToken,AttributeType=S \
    --table-name ActorUsers \
    --key-schema AttributeName=Id,KeyType=HASH \
    --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10 \
    --region eu-west-1 \
    --endpoint $DYNAMODN_ENDPOINT \
    --global-secondary-indexes \
      IndexName=EmailIndex,KeySchema=["{AttributeName=Email,KeyType=HASH}"],Projection="{ProjectionType=ALL}",ProvisionedThroughput="{ReadCapacityUnits=10,WriteCapacityUnits=10}" \
      IndexName=ActivationTokenIndex,KeySchema=["{AttributeName=ActivationToken,KeyType=HASH}"],Projection="{ProjectionType=KEYS_ONLY}",ProvisionedThroughput="{ReadCapacityUnits=10,WriteCapacityUnits=10}" \
      IndexName=PasswordResetTokenIndex,KeySchema=["{AttributeName=PasswordResetToken,KeyType=HASH}"],Projection="{ProjectionType=KEYS_ONLY}",ProvisionedThroughput="{ReadCapacityUnits=10,WriteCapacityUnits=10}"

    aws dynamodb update-time-to-live \
    --table-name ActorUsers \
    --region eu-west-1 \
    --endpoint $DYNAMODN_ENDPOINT \
    --time-to-live-specification "Enabled=true, AttributeName=ExpiresTTL"

    aws dynamodb create-table \
    --attribute-definitions AttributeName=ViewerCode,AttributeType=S AttributeName=SiriusUid,AttributeType=S AttributeName=Expires,AttributeType=S \
    --table-name ViewerCodes \
    --key-schema AttributeName=ViewerCode,KeyType=HASH \
    --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10 \
    --region eu-west-1 \
    --endpoint $DYNAMODN_ENDPOINT \
    --global-secondary-indexes IndexName=SiriusUidIndex,KeySchema=["{AttributeName=SiriusUid,KeyType=HASH},{AttributeName=Expires,KeyType=RANGE}"],Projection="{ProjectionType=ALL}",ProvisionedThroughput="{ReadCapacityUnits=10,WriteCapacityUnits=10}"

    aws dynamodb create-table \
    --attribute-definitions AttributeName=ViewerCode,AttributeType=S AttributeName=Viewed,AttributeType=S \
    --table-name ViewerActivity \
    --key-schema AttributeName=ViewerCode,KeyType=HASH AttributeName=Viewed,KeyType=RANGE \
    --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10 \
    --region eu-west-1 \
    --endpoint $DYNAMODN_ENDPOINT

    aws dynamodb create-table \
    --attribute-definitions AttributeName=Id,AttributeType=S AttributeName=UserId,AttributeType=S \
    --table-name UserLpaActorMap \
    --key-schema AttributeName=Id,KeyType=HASH \
    --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10 \
    --region eu-west-1 \
    --endpoint $DYNAMODN_ENDPOINT \
    --global-secondary-indexes IndexName=UserIndex,KeySchema=["{AttributeName=UserId,KeyType=HASH}"],Projection="{ProjectionType=ALL}",ProvisionedThroughput="{ReadCapacityUnits=10,WriteCapacityUnits=10}"

fi

if ! [[ -z "${CODES_ENDPOINT}" ]]; then
  # Setup the local codes service
  /usr/local/bin/waitforit -address=tcp://${CODES_ENDPOINT}:4343 -timeout 60 -retry 6000 -debug
  curl -X POST -H 'Authorization: asdf1234567890' http://${CODES_ENDPOINT}/setup/dynamodb/create/table
fi

# Run the seeding script
# This is written in Python as managing the variable DynamoDB endpoint isn't easy in a shell script.
python /app/seeding/dynamodb.py
python /app/seeding/put_actor_codes.py -f /app/seeding/seeding_lpa_codes.json -d

echo Finished seeding
