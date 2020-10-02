import React from 'react';
import styled from "styled-components";

const translate = require('oro/translator');

const Message = styled.div`
  text-align: center;
  width: 100%;
  margin-top: 100px;
`;

const Title = styled.div`
  font-size: 28px;
  color: #11324d;
  margin-top: 5px;
`;

const Subtitle = styled.div`
  font-size: 15px;
  margin-top: 10px;
`;

const HelpCenterLink = styled.a`
  font-size: 15px;
  color: #9452BA;
  cursor: pointer;
  margin-top: 5px;
  text-decoration: underline;
`;

const NoAttributeGroups = () => {
  return (
    <Message>
      <img src="bundles/akeneodataqualityinsights/images/QualityScore.svg"/>
      <Title>{translate('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.title')}</Title>
      <Subtitle>{translate('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.subtitle')}</Subtitle>
      <HelpCenterLink href="https://help.akeneo.com" target="_blank">
        {translate('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.help_center_link')}
      </HelpCenterLink>
    </Message>
  );
};

export {NoAttributeGroups};
