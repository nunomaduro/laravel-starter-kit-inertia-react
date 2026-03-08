import{j as e}from"./jsx-runtime-u17CrQMm.js";import{r as D}from"./index-CAT4JDgN.js";import{c as p}from"./utils-BQHNewu7.js";import{S as A}from"./stat-card-BrzgDgr-.js";import{A as N}from"./area-chart-KOnd43kE.js";import{B as M}from"./bar-chart-D_Hpmrqw.js";import{C as q,a as K,b as w,d as R}from"./card-D9sG6r10.js";import{T as k,a as L,b as S}from"./tabs-C5KJ5DR1.js";import{U as _,T as V}from"./users-C5NjE_v4.js";import{c as E}from"./createLucideIcon-Cq8_ABKM.js";import{A as H}from"./activity-o_9qHl6w.js";import"./_commonjsHelpers-CE1G-McA.js";import"./badge-dtsZ3OQs.js";import"./index-yvwtsnL6.js";import"./index-dmJXtalC.js";import"./index-LHNt3CwB.js";import"./skeleton-CuxgNOSS.js";import"./use-reduced-motion-Bb6b8sk-.js";import"./chart-colors-wMYsAnQC.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-BAEXrmqL.js";import"./AreaChart-DnA8_UZz.js";import"./getRadiusAndStrokeWidthFromDot-CHDRXA91.js";import"./CartesianChart-CkFwa3sR.js";import"./graphicalItemSelectors-CWGmRhZ6.js";import"./YAxis-C0ByQhTu.js";import"./getClassNameFromUnknown-wb-VDXTZ.js";import"./tooltipContext-BggRGtMu.js";import"./ActiveShapeUtils-Caemlefb.js";import"./ErrorBarContext-X7aR2pnw.js";import"./index-CFnpPvE6.js";import"./index-BMy6K8Gt.js";import"./index-BMkthtSq.js";import"./index-BZKZbRGl.js";import"./index-DegZLnM5.js";import"./index-CfYvYFbm.js";const I=[["line",{x1:"12",x2:"12",y1:"2",y2:"22",key:"7eqyqh"}],["path",{d:"M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6",key:"1b0p4s"}]],P=E("dollar-sign",I);function s({metrics:r,chartTitle:v="Overview",chartData:n=[],chartXKey:u="date",chartDataKeys:m=["value"],chartType:h="area",periodOptions:t,onPeriodChange:y,className:b,isLoading:d=!1,columns:f=4}){const[T,x]=D.useState(t?.[0]?.value??""),C=a=>{x(a),y?.(a)},j={2:"grid-cols-1 sm:grid-cols-2",3:"grid-cols-1 sm:grid-cols-2 lg:grid-cols-3",4:"grid-cols-1 sm:grid-cols-2 lg:grid-cols-4"};return e.jsxs("div",{"data-slot":"metric-dashboard",className:p("space-y-4",b),children:[e.jsx("div",{className:p("grid gap-4",j[f]),children:r.map(a=>e.jsx(A,{title:a.title,value:a.value,description:a.description,icon:a.icon,trend:a.trend,badge:a.badge,isLoading:a.isLoading??d},a.id))}),n.length>0&&e.jsxs(q,{children:[e.jsxs(K,{className:"flex flex-row items-center justify-between pb-2",children:[e.jsx(w,{className:"text-sm font-medium",children:v}),t&&t.length>0&&e.jsx(k,{value:T,onValueChange:C,children:e.jsx(L,{className:"h-7",children:t.map(a=>e.jsx(S,{value:a.value,className:"px-2.5 text-xs",children:a.label},a.value))})})]}),e.jsx(R,{className:"pb-4",children:h==="area"?e.jsx(N,{data:n,xKey:u,dataKeys:m,height:220,skeleton:d}):e.jsx(M,{data:n,xKey:u,dataKeys:m,height:220,skeleton:d})})]})]})}s.__docgenInfo={description:"",methods:[],displayName:"MetricDashboard",props:{metrics:{required:!0,tsType:{name:"Array",elements:[{name:"MetricCardConfig"}],raw:"MetricCardConfig[]"},description:""},chartTitle:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:"'Overview'",computed:!1}},chartData:{required:!1,tsType:{name:"Array",elements:[{name:"ChartDataPoint"}],raw:"ChartDataPoint[]"},description:"",defaultValue:{value:"[]",computed:!1}},chartXKey:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:"'date'",computed:!1}},chartDataKeys:{required:!1,tsType:{name:"Array",elements:[{name:"string"}],raw:"string[]"},description:"",defaultValue:{value:"['value']",computed:!1}},chartType:{required:!1,tsType:{name:"union",raw:"'area' | 'bar'",elements:[{name:"literal",value:"'area'"},{name:"literal",value:"'bar'"}]},description:"",defaultValue:{value:"'area'",computed:!1}},periodOptions:{required:!1,tsType:{name:"Array",elements:[{name:"signature",type:"object",raw:"{ label: string; value: string }",signature:{properties:[{key:"label",value:{name:"string",required:!0}},{key:"value",value:{name:"string",required:!0}}]}}],raw:"{ label: string; value: string }[]"},description:""},onPeriodChange:{required:!1,tsType:{name:"signature",type:"function",raw:"(value: string) => void",signature:{arguments:[{type:{name:"string"},name:"value"}],return:{name:"void"}}},description:""},className:{required:!1,tsType:{name:"string"},description:""},isLoading:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},columns:{required:!1,tsType:{name:"union",raw:"2 | 3 | 4",elements:[{name:"literal",value:"2"},{name:"literal",value:"3"},{name:"literal",value:"4"}]},description:"",defaultValue:{value:"4",computed:!1}}}};const g=[{date:"Jan",revenue:8400,users:1200},{date:"Feb",revenue:10500,users:1500},{date:"Mar",revenue:9450,users:1350},{date:"Apr",revenue:12600,users:1800},{date:"May",revenue:14700,users:2100},{date:"Jun",revenue:16800,users:2400}],c=[{id:"users",title:"Total Users",value:"12,345",description:"Active accounts",icon:e.jsx(_,{className:"size-4"}),trend:{value:8.2,direction:"up",label:"8.2%"}},{id:"revenue",title:"Monthly Revenue",value:"$48,230",description:"vs last month",icon:e.jsx(P,{className:"size-4"}),trend:{value:12.5,direction:"up",label:"12.5%"}},{id:"growth",title:"Growth Rate",value:"23.4%",description:"Month over month",icon:e.jsx(V,{className:"size-4"}),trend:{value:3.1,direction:"up"}},{id:"churn",title:"Churn Rate",value:"2.3%",description:"This quarter",icon:e.jsx(H,{className:"size-4"}),trend:{value:-.4,direction:"down"}}],De={title:"Composed/MetricDashboard",component:s,tags:["autodocs"],parameters:{layout:"fullscreen"},argTypes:{chartType:{control:"select",options:["area","bar"]},columns:{control:"select",options:[2,3,4]},isLoading:{control:"boolean"}}},i={args:{metrics:c,chartData:g,chartXKey:"date",chartDataKeys:["revenue"],chartTitle:"Revenue Over Time",periodOptions:[{label:"Last 6 months",value:"6m"},{label:"Last year",value:"1y"},{label:"All time",value:"all"}],columns:4},render:r=>e.jsx("div",{className:"p-6 bg-background",children:e.jsx(s,{...r})})},l={args:{metrics:c,chartData:[],chartDataKeys:["revenue"],chartXKey:"date",isLoading:!0},render:r=>e.jsx("div",{className:"p-6 bg-background",children:e.jsx(s,{...r})})},o={args:{metrics:c.slice(0,2),chartData:g,chartXKey:"date",chartDataKeys:["revenue","users"],chartType:"bar",chartTitle:"Revenue vs Users",columns:2},render:r=>e.jsx("div",{className:"p-6 bg-background",children:e.jsx(s,{...r})})};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  args: {
    metrics: METRICS,
    chartData: CHART_DATA,
    chartXKey: 'date',
    chartDataKeys: ['revenue'],
    chartTitle: 'Revenue Over Time',
    periodOptions: [{
      label: 'Last 6 months',
      value: '6m'
    }, {
      label: 'Last year',
      value: '1y'
    }, {
      label: 'All time',
      value: 'all'
    }],
    columns: 4
  },
  render: args => <div className="p-6 bg-background"><MetricDashboard {...args} /></div>
}`,...i.parameters?.docs?.source}}};l.parameters={...l.parameters,docs:{...l.parameters?.docs,source:{originalSource:`{
  args: {
    metrics: METRICS,
    chartData: [],
    chartDataKeys: ['revenue'],
    chartXKey: 'date',
    isLoading: true
  },
  render: args => <div className="p-6 bg-background"><MetricDashboard {...args} /></div>
}`,...l.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    metrics: METRICS.slice(0, 2),
    chartData: CHART_DATA,
    chartXKey: 'date',
    chartDataKeys: ['revenue', 'users'],
    chartType: 'bar',
    chartTitle: 'Revenue vs Users',
    columns: 2
  },
  render: args => <div className="p-6 bg-background"><MetricDashboard {...args} /></div>
}`,...o.parameters?.docs?.source}}};const Ae=["Default","Loading","BarChart"];export{o as BarChart,i as Default,l as Loading,Ae as __namedExportsOrder,De as default};
