import{j as e}from"./jsx-runtime-u17CrQMm.js";import{A as s}from"./area-chart-KOnd43kE.js";import"./utils-BQHNewu7.js";import"./use-reduced-motion-Bb6b8sk-.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./skeleton-CuxgNOSS.js";import"./chart-colors-wMYsAnQC.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-BAEXrmqL.js";import"./AreaChart-DnA8_UZz.js";import"./getRadiusAndStrokeWidthFromDot-CHDRXA91.js";import"./CartesianChart-CkFwa3sR.js";import"./graphicalItemSelectors-CWGmRhZ6.js";import"./YAxis-C0ByQhTu.js";import"./getClassNameFromUnknown-wb-VDXTZ.js";const d=[{month:"Jan",users:1200,revenue:8400},{month:"Feb",users:1500,revenue:10500},{month:"Mar",users:1350,revenue:9450},{month:"Apr",users:1800,revenue:12600},{month:"May",users:2100,revenue:14700},{month:"Jun",users:2400,revenue:16800},{month:"Jul",users:2250,revenue:15750},{month:"Aug",users:2700,revenue:18900},{month:"Sep",users:3e3,revenue:21e3},{month:"Oct",users:2850,revenue:19950},{month:"Nov",users:3300,revenue:23100},{month:"Dec",users:3600,revenue:25200}],j={title:"Charts/AreaChart",component:s,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{stacked:{control:"boolean"},showGrid:{control:"boolean"},showLegend:{control:"boolean"},showTooltip:{control:"boolean"},skeleton:{control:"boolean"},height:{control:{type:"range",min:100,max:600,step:50}}}},a={args:{data:d,dataKeys:["users"],xKey:"month",height:300},render:r=>e.jsx("div",{className:"w-[600px]",children:e.jsx(s,{...r})})},t={args:{data:d,dataKeys:["users","revenue"],xKey:"month",showLegend:!0,height:300},render:r=>e.jsx("div",{className:"w-[600px]",children:e.jsx(s,{...r})})},o={args:{data:d,dataKeys:["users","revenue"],xKey:"month",stacked:!0,showLegend:!0,height:300},render:r=>e.jsx("div",{className:"w-[600px]",children:e.jsx(s,{...r})})},n={args:{data:[],dataKeys:["value"],xKey:"date",skeleton:!0,height:300},render:r=>e.jsx("div",{className:"w-[600px]",children:e.jsx(s,{...r})})};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  args: {
    data: MONTHLY_DATA,
    dataKeys: ['users'],
    xKey: 'month',
    height: 300
  },
  render: args => <div className="w-[600px]"><AreaChart {...args} /></div>
}`,...a.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    data: MONTHLY_DATA,
    dataKeys: ['users', 'revenue'],
    xKey: 'month',
    showLegend: true,
    height: 300
  },
  render: args => <div className="w-[600px]"><AreaChart {...args} /></div>
}`,...t.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    data: MONTHLY_DATA,
    dataKeys: ['users', 'revenue'],
    xKey: 'month',
    stacked: true,
    showLegend: true,
    height: 300
  },
  render: args => <div className="w-[600px]"><AreaChart {...args} /></div>
}`,...o.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  args: {
    data: [],
    dataKeys: ['value'],
    xKey: 'date',
    skeleton: true,
    height: 300
  },
  render: args => <div className="w-[600px]"><AreaChart {...args} /></div>
}`,...n.parameters?.docs?.source}}};const k=["SingleSeries","MultiSeries","Stacked","LoadingSkeleton"];export{n as LoadingSkeleton,t as MultiSeries,a as SingleSeries,o as Stacked,k as __namedExportsOrder,j as default};
